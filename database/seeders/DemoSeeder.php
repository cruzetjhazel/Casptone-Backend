<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\CustomPackageComponentType;
use App\Enums\PackageStatus;
use App\Enums\PaymentPlan;
use App\Enums\PhotographerApplicationStatus;
use App\Enums\PhotographerType;
use App\Enums\ReportRequestedAction;
use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use App\Enums\ReportTargetType;
use App\Enums\ServiceTrackerStatus;
use App\Models\ActivityLog;
use App\Models\AddOn;
use App\Models\AvailabilityWindow;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\ClientProfile;
use App\Models\CustomPackageComponent;
use App\Models\CustomPackageConfig;
use App\Models\FavoritePhotographer;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PhotographerApplication;
use App\Models\PhotographerPaymentConfig;
use App\Models\PhotographerPaymentReference;
use App\Models\PhotographerPortfolioImage;
use App\Models\PhotographerProfile;
use App\Models\ProfileView;
use App\Models\Report;
use App\Models\ReportNote;
use App\Models\Review;
use App\Models\ServiceSearchLog;
use App\Models\User;
use App\Models\WalkInClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Populates a full demo dataset: clients, photographers (freelancer + studio,
 * across every application status), packages/add-ons/custom-package setups,
 * availability, bookings across every status, payments, reviews, reports,
 * activity logs, profile views, and service search logs.
 *
 * Only runs in local/testing, same guard as AdminSeeder. Run after
 * AdminSeeder so an administrator exists to attribute admin-side actions to.
 */
class DemoSeeder extends Seeder
{
    private const PHOTOGRAPHER_COUNT = 10;

    private const FREELANCER_COUNT = 6; // remaining PHOTOGRAPHER_COUNT - FREELANCER_COUNT are studios

    private const CLIENT_COUNT = 20;

    // Deterministic palette for generated profile/cover placeholder images,
    // cycled by photographer index so colors stay stable across reseeds.
    private const AVATAR_COLORS = [
        '#6D28D9', '#DB2777', '#2563EB', '#059669', '#D97706',
        '#DC2626', '#0891B2', '#7C3AED', '#EA580C', '#4F46E5',
    ];

    private const SEARCH_TERMS = [
        'wedding photographer', 'prenup shoot', 'debut photographer',
        'birthday photographer', 'graduation photos', 'corporate event photographer',
        'newborn photoshoot', 'family portrait', 'christening photographer',
        'product photography', 'studio photographer near me', 'outdoor prenup',
        'photographer with drone', 'affordable wedding package', 'photo and video package',
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('DemoSeeder only runs in local/testing.');

            return;
        }

        $admin = User::where('account_type', AccountType::Administrator)->first();

        $this->command->info('Seeding clients...');
        $clients = $this->createClients();

        $this->command->info('Seeding photographers...');
        $photographers = $this->createPhotographers();
        $approved = $photographers->filter(
            fn (array $p) => $p['application']->status === PhotographerApplicationStatus::Approved
        )->values();

        $this->command->info('Seeding favorites...');
        $this->createFavorites($clients, $approved);

        $this->command->info('Seeding bookings, payments & reviews...');
        $bookings = $this->createBookings($clients, $approved, $admin);

        $this->command->info('Seeding reports...');
        $this->createReports($clients, $approved, $admin, $bookings);

        $this->command->info('Seeding activity logs...');
        $this->createActivityLogs($admin, $photographers, $bookings);

        $this->command->info('Seeding profile views & search logs...');
        $this->createProfileViews($approved);
        $this->createSearchLogs();

        $this->command->info(sprintf(
            'Demo data seeded: %d clients, %d photographers (%d approved), %d bookings.',
            $clients->count(),
            $photographers->count(),
            $approved->count(),
            $bookings->count(),
        ));
    }

    /** @return Collection<int, User> */
    private function createClients(): Collection
    {
        return collect(range(1, self::CLIENT_COUNT))->map(function () {
            $client = User::factory()->create();

            ClientProfile::factory()->create(['user_id' => $client->id]);

            return $client;
        });
    }

    /**
     * @return Collection<int, array{user: User, application: PhotographerApplication, type: PhotographerType}>
     */
    private function createPhotographers(): Collection
    {
        return collect(range(0, self::PHOTOGRAPHER_COUNT - 1))->map(function (int $i) {
            $type = $i < self::FREELANCER_COUNT ? PhotographerType::Freelancer : PhotographerType::Studio;

            // Override only the requested demo photographer names.
            // Indexes 0-5 are freelancers; indexes 6-9 are studios.
            // Keep the remaining photographers factory-generated.
            $showcaseNames = [
                0 => 'CJ Creatives',
                1 => 'Joesol Photography',
                2 => 'Frederick Robelas',
                6 => 'HHProduction',
                7 => 'KAP Studio',
                8 => 'Amaras Studio',
            ];

            $user = User::factory()->photographer()->create(
                isset($showcaseNames[$i]) ? ['name' => $showcaseNames[$i]] : []
            );

            // Distribution across the 10 photographers:
            // - 8 approved (5 freelancers, 3 studios) — fully set up businesses
            // - 1 pending_review (last freelancer)   — awaiting admin decision
            // - 1 revision_requested (last studio)    — sent back for fixes
            $status = match (true) {
                $i === self::FREELANCER_COUNT - 1 => 'pending_review',
                $i === self::PHOTOGRAPHER_COUNT - 1 => 'revision_requested',
                default => 'approved',
            };

            $applicationFactory = PhotographerApplication::factory()
                ->state(['user_id' => $user->id, 'photographer_type' => $type]);

            if ($type === PhotographerType::Studio) {
                $applicationFactory = $applicationFactory->studio();
            }

            $reviewer = User::where('account_type', AccountType::Administrator)->first();

            $application = match ($status) {
                'approved' => $applicationFactory->approved()->create(['reviewed_by' => $reviewer?->id]),
                'pending_review' => $applicationFactory->pendingReview()->create(),
                'revision_requested' => $applicationFactory->revisionRequested()->create(['reviewed_by' => $reviewer?->id]),
            };

            $imagePaths = $status === 'approved'
                ? $this->seedProfileImages($i, $user->name)
                : ['profile' => null, 'cover' => null];

            $profile = PhotographerProfile::factory()->create([
                'user_id' => $user->id,
                'bio' => $status === 'approved' ? fake()->paragraph() : null,
                'style' => $status === 'approved' ? fake()->randomElements(
                    ['Candid', 'Documentary', 'Fine Art', 'Traditional', 'Cinematic'],
                    fake()->numberBetween(1, 3)
                ) : null,
                'profile_photo_path' => $imagePaths['profile'],
                'cover_photo_path' => $imagePaths['cover'],
                'facebook' => $status === 'approved' ? 'https://facebook.com/'.fake()->userName() : null,
            ]);

            $packages = collect();
            $addOns = collect();

            if ($status === 'approved') {
                $this->setupApprovedPhotographerBusiness($user);
                $packages = Package::where('user_id', $user->id)->get();
                $addOns = AddOn::where('user_id', $user->id)->get();
            }

            return [
                'user' => $user,
                'application' => $application,
                'profile' => $profile,
                'type' => $type,
                'packages' => $packages,
                'add_ons' => $addOns,
            ];
        });
    }

    /**
     * Generates real placeholder JPEG files on the `public` disk and returns
     * the paths to store on the profile, instead of writing a fake path that
     * points to a file which never actually exists on disk (the previous
     * behavior — every approved photographer's card showed a broken image).
     *
     * Files are generated once per (index, name) pair and reused on
     * subsequent reseeds (checked via Storage::exists) so `migrate:fresh
     * --seed` doesn't regenerate images it already has.
     *
     * @return array{profile: string|null, cover: string|null}
     */
    private function seedProfileImages(int $index, string $name): array
    {
        if (! extension_loaded('gd')) {
            $this->command->warn("GD extension not available — skipping demo image generation for \"{$name}\". Enable php-gd to seed profile/cover images.");

            return ['profile' => null, 'cover' => null];
        }

        $slug = Str::slug($name) ?: 'photographer';
        $color = self::AVATAR_COLORS[$index % count(self::AVATAR_COLORS)];
        $initials = collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->map(fn (string $word) => strtoupper($word[0]))
            ->take(2)
            ->implode('');

        $profilePath = "photographers/seed/{$slug}-{$index}-profile.jpg";
        $coverPath = "photographers/seed/{$slug}-{$index}-cover.jpg";

        if (! Storage::disk('public')->exists($profilePath)) {
            Storage::disk('public')->put($profilePath, $this->generatePlaceholderImage($initials ?: 'PH', 400, 400, $color));
        }

        if (! Storage::disk('public')->exists($coverPath)) {
            Storage::disk('public')->put($coverPath, $this->generatePlaceholderImage($name, 1200, 400, $color));
        }

        return ['profile' => $profilePath, 'cover' => $coverPath];
    }

    /**
     * Renders a solid-color JPEG with a centered text label using GD.
     * No external network calls or bundled image assets required, so this
     * works offline and never depends on a third-party placeholder service
     * going down.
     */
    private function generatePlaceholderImage(string $label, int $width, int $height, string $hexColor): string
    {
        $image = imagecreatetruecolor($width, $height);

        [$r, $g, $b] = sscanf($hexColor, '#%02x%02x%02x');
        $background = imagecolorallocate($image, (int) $r, (int) $g, (int) $b);
        imagefill($image, 0, 0, $background);

        $white = imagecolorallocate($image, 255, 255, 255);
        $font = 5; // largest built-in GD bitmap font, no TTF file needed
        $textWidth = imagefontwidth($font) * strlen($label);
        $textHeight = imagefontheight($font);
        $x = max((int) (($width - $textWidth) / 2), 4);
        $y = max((int) (($height - $textHeight) / 2), 4);
        imagestring($image, $font, $x, $y, $label, $white);

        ob_start();
        imagejpeg($image, quality: 85);
        $contents = ob_get_clean();
        imagedestroy($image);

        return $contents;
    }

    private function setupApprovedPhotographerBusiness(User $photographer): void
    {
        // Portfolio: 5-8 images, mostly active, a couple archived.
        PhotographerPortfolioImage::factory()
            ->count(fake()->numberBetween(5, 8))
            ->create(['user_id' => $photographer->id]);
        PhotographerPortfolioImage::factory()
            ->archived()
            ->count(fake()->numberBetween(0, 2))
            ->create(['user_id' => $photographer->id]);

        // Packages: 2-4, mostly published, one draft, occasionally one archived.
        $packageNames = [
            ['name' => 'Basic Wedding Package', 'price' => 12000, 'included_items' => ['4 hours coverage', '150 edited photos', 'Online gallery']],
            ['name' => 'Premium Wedding Package', 'price' => 25000, 'included_items' => ['8 hours coverage', '400 edited photos', 'Same-day teaser video', 'Printed album']],
            ['name' => 'Debut Package', 'price' => 15000, 'included_items' => ['6 hours coverage', '200 edited photos', 'Photobook']],
            ['name' => 'Corporate Event Package', 'price' => 9000, 'included_items' => ['4 hours coverage', '100 edited photos']],
        ];
        $chosen = collect($packageNames)->random(fake()->numberBetween(2, 4));
        foreach ($chosen as $i => $pkg) {
            $status = match (true) {
                $i === 0 => PackageStatus::Draft,
                default => PackageStatus::Published,
            };

            Package::factory()->create([
                'user_id' => $photographer->id,
                'name' => $pkg['name'],
                'included_items' => $pkg['included_items'],
                'price' => $pkg['price'],
                'status' => $status,
            ]);
        }
        // Occasionally archive an old package too.
        if (fake()->boolean(30)) {
            Package::factory()->archived()->create([
                'user_id' => $photographer->id,
                'name' => 'Old '.fake()->year().' Rate Card',
            ]);
        }

        // Add-ons: 1-3.
        $addOnOptions = [
            ['name' => 'Extra Hour Coverage', 'price' => 1500],
            ['name' => 'Drone Shots', 'price' => 2500],
            ['name' => 'Same-Day Edit Video', 'price' => 5000],
            ['name' => 'Printed Photobook', 'price' => 3000],
            ['name' => 'RAW Files', 'price' => 2000],
        ];
        foreach (collect($addOnOptions)->random(fake()->numberBetween(1, 3)) as $addOn) {
            AddOn::factory()->create([
                'user_id' => $photographer->id,
                'name' => $addOn['name'],
                'description' => $addOn['name'].' add-on for your booking.',
                'price' => $addOn['price'],
            ]);
        }

        // Custom packages: ~60% of approved photographers offer one.
        if (fake()->boolean(60)) {
            CustomPackageConfig::factory()->create([
                'user_id' => $photographer->id,
                'enabled' => true,
                'base_fee' => fake()->randomElement([1500, 2000, 3000]),
                'buffer_minutes' => 30,
            ]);

            $this->createCustomPackageComponents($photographer);
        }

        // Availability: ~8 upcoming open windows over the next 30 days.
        for ($i = 0; $i < 8; $i++) {
            AvailabilityWindow::factory()->create([
                'user_id' => $photographer->id,
                'date' => now()->addDays(fake()->numberBetween(2, 30))->format('Y-m-d'),
            ]);
        }

        // Blocked dates: 1-2.
        BlockedDate::factory()->count(fake()->numberBetween(1, 2))->create([
            'user_id' => $photographer->id,
        ]);

        // GCash payout config.
        PhotographerPaymentConfig::factory()->create(['user_id' => $photographer->id]);

        // Payment references: 3-5, mostly used/available, occasionally invalidated.
        $refCount = fake()->numberBetween(3, 5);
        for ($i = 0; $i < $refCount; $i++) {
            $state = fake()->randomElement(['available', 'available', 'used', 'used', 'invalidated']);
            $factory = PhotographerPaymentReference::factory();
            $factory = match ($state) {
                'used' => $factory->used(),
                'invalidated' => $factory->invalidated(),
                default => $factory,
            };
            $factory->create(['photographer_id' => $photographer->id]);
        }

        // Walk-in clients recorded outside the platform: 2-4.
        WalkInClient::factory()->count(fake()->numberBetween(2, 4))->create([
            'photographer_id' => $photographer->id,
        ]);
    }

    private function createCustomPackageComponents(User $photographer): void
    {
        $durationTiers = [
            ['label' => '2 Hours Coverage', 'duration_minutes' => 120, 'price_addition' => 0],
            ['label' => '4 Hours Coverage', 'duration_minutes' => 240, 'price_addition' => 1500],
            ['label' => '6 Hours Coverage', 'duration_minutes' => 360, 'price_addition' => 3000],
        ];
        foreach ($durationTiers as $tier) {
            CustomPackageComponent::factory()->create([
                'user_id' => $photographer->id,
                'type' => CustomPackageComponentType::TierOption,
                'tier_name' => 'Coverage Duration',
                'label' => $tier['label'],
                'price_addition' => $tier['price_addition'],
                'duration_minutes' => $tier['duration_minutes'],
            ]);
        }

        $photoTiers = [
            ['label' => '150 Edited Photos', 'price_addition' => 0],
            ['label' => '300 Edited Photos', 'price_addition' => 2000],
        ];
        foreach ($photoTiers as $tier) {
            CustomPackageComponent::factory()->create([
                'user_id' => $photographer->id,
                'type' => CustomPackageComponentType::TierOption,
                'tier_name' => 'Edited Photos',
                'label' => $tier['label'],
                'price_addition' => $tier['price_addition'],
                'duration_minutes' => null,
            ]);
        }

        CustomPackageComponent::factory()->create([
            'user_id' => $photographer->id,
            'type' => CustomPackageComponentType::FlatOption,
            'tier_name' => null,
            'label' => 'Drone Coverage',
            'price_addition' => 2500,
            'duration_minutes' => null,
        ]);
    }

    /**
     * @param  Collection<int, User>  $clients
     * @param  Collection<int, array{user: User}>  $approved
     */
    private function createFavorites(Collection $clients, Collection $approved): void
    {
        foreach ($clients as $client) {
            $picks = $approved->random(min(fake()->numberBetween(0, 3), $approved->count()));
            foreach ($picks as $photographer) {
                FavoritePhotographer::firstOrCreate([
                    'client_id' => $client->id,
                    'photographer_id' => $photographer['user']->id,
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, User>  $clients
     * @param  Collection<int, array{user: User, packages: Collection}>  $approved
     * @return Collection<int, Booking>
     */
    private function createBookings(Collection $clients, Collection $approved, ?User $admin): Collection
    {
        // BookingStatus only has 5 cases: Pending, Confirmed, Completed,
        // Cancelled, Expired. There's no separate "Accepted" status —
        // AcceptBookingAction moves Pending straight to Confirmed — and no
        // "Rejected" status — RejectBookingAction moves Pending to Cancelled
        // with rejection_reason set. The scenario keys below name the
        // *situation* being demoed, not a status value, so applyBookingStatus
        // can map each one to a real, valid status.
        $plan = [
            'completed' => 16,
            'confirmed_paid' => 7,
            'confirmed_awaiting_payment' => 5, // mirrors AcceptBookingAction: Pending -> Confirmed, nothing else touched
            'pending' => 6,
            'cancelled_rejected' => 5, // mirrors RejectBookingAction: Pending -> Cancelled + rejection_reason
            'cancelled_after_confirm' => 4, // client-initiated cancellation after the booking was confirmed
            'expired' => 2,
        ];

        $bookings = collect();
        $eventTypes = ['wedding', 'debut', 'birthday', 'corporate', 'graduation', 'christening'];
        $locationTypes = ['studio', 'client_location', 'outdoor_location', 'other'];

        foreach ($plan as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $client = $clients->random();
                $photographer = $approved->random();
                $publishedPackages = $photographer['packages']->where('status', PackageStatus::Published);
                $package = $publishedPackages->count() > 0 ? $publishedPackages->random() : null;

                $price = $package?->price ?? fake()->randomElement([10000, 12000, 15000, 20000]);
                $daysOffset = match ($status) {
                    'completed' => -fake()->numberBetween(10, 180),
                    'confirmed_paid' => fake()->numberBetween(3, 60),
                    'confirmed_awaiting_payment' => fake()->numberBetween(5, 45),
                    'pending' => fake()->numberBetween(3, 30),
                    'cancelled_rejected' => fake()->numberBetween(3, 30),
                    'cancelled_after_confirm' => fake()->numberBetween(-30, 30),
                    'expired' => fake()->numberBetween(3, 20),
                };

                $booking = Booking::factory()->create([
                    'client_id' => $client->id,
                    'photographer_id' => $photographer['user']->id,
                    'package_id' => $package?->id,
                    'is_custom_package' => false,
                    'package_snapshot' => $package ? ['name' => $package->name, 'price' => (float) $package->price] : null,
                    'add_ons_snapshot' => [],
                    'event_type' => fake()->randomElement($eventTypes),
                    'event_date' => now()->addDays($daysOffset)->format('Y-m-d'),
                    'start_time' => '09:00',
                    'end_time' => '13:00',
                    'location_type' => fake()->randomElement($locationTypes),
                    'event_address' => fake()->address(),
                    'guest_count' => fake()->numberBetween(20, 250),
                    'subtotal' => $price,
                    'total_price' => $price,
                    'status' => BookingStatus::Pending,
                    'hold_expires_at' => now()->addHours(24),
                ]);

                $this->applyBookingStatus($booking, $status, $admin);
                $bookings->push($booking->fresh());
            }
        }

        return $bookings;
    }

    private function applyBookingStatus(Booking $booking, string $status, ?User $admin): void
    {
        $plan = fake()->randomElement([PaymentPlan::Half, PaymentPlan::Full]);

        match ($status) {
            'pending' => null, // stays as created: Pending, hold_expires_at in the future

            // Mirrors AcceptBookingAction::execute() exactly: only status and
            // hold_expires_at change. No payment fields, no service_status —
            // that's the true state right after a photographer clicks Accept.
            'confirmed_awaiting_payment' => $booking->update([
                'status' => BookingStatus::Confirmed,
                'hold_expires_at' => null,
            ]),

            // Mirrors RejectBookingAction::execute() exactly: Cancelled +
            // rejection_reason, nothing else. The UI is expected to tell
            // these apart from client-initiated cancellations by checking
            // whether rejection_reason is set.
            'cancelled_rejected' => $booking->update([
                'status' => BookingStatus::Cancelled,
                'rejection_reason' => fake()->randomElement([
                    'Not available on the requested date.',
                    'Requested package no longer offered.',
                    'Fully booked for that week.',
                ]),
                'hold_expires_at' => null,
            ]),

            'expired' => $booking->update([
                'status' => BookingStatus::Expired,
                'hold_expires_at' => now()->subDay(),
            ]),

            'cancelled_after_confirm' => $this->applyCancelled($booking, $plan, $admin),

            'confirmed_paid' => $this->applyConfirmed($booking, $plan, $admin),

            'completed' => $this->applyCompleted($booking, $plan, $admin),

            default => null,
        };
    }

    private function applyCancelled(Booking $booking, PaymentPlan $plan, ?User $admin): void
    {
        $wasConfirmedFirst = fake()->boolean(50);
        $decision = fake()->randomElement(['approved', 'rejected']);

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'hold_expires_at' => null,
            'payment_plan' => $wasConfirmedFirst ? $plan : null,
            'payment_status' => $wasConfirmedFirst ? BookingPaymentStatus::Cancelled : BookingPaymentStatus::Pending,
            'cancellation_reason' => fake()->randomElement([
                'Change of event date conflicts with photographer availability.',
                'Client found another provider.',
                'Family emergency.',
            ]),
            'cancellation_requested_at' => now()->subDays(fake()->numberBetween(1, 10)),
            'cancellation_decision' => $decision,
            'cancellation_decided_at' => now()->subDays(fake()->numberBetween(0, 5)),
        ]);

        if ($wasConfirmedFirst) {
            $this->createPaymentsForBooking($booking, $plan, onlyFirstInstallment: true, admin: $admin);
        }
    }

    private function applyConfirmed(Booking $booking, PaymentPlan $plan, ?User $admin): void
    {
        // Most confirmed bookings have at least started payment; a couple sit
        // at "pending_verification" to demo the admin review queue.
        $verificationQueueCase = fake()->boolean(20);

        // NOTE: migration 2026_09_07_082733_fix_service_status_check_constraint_table
        // narrowed chk_booking_service_status to ServiceTrackerStatus's real
        // 3 cases (EventDay/Editing/Delivered). The old 6-value set used here
        // previously is retired — that mismatch is why every booking after
        // the first one aborted the seeder before it could reach payments or
        // reports.
        $serviceStatus = fake()->randomElement([ServiceTrackerStatus::EventDay, ServiceTrackerStatus::Editing]);

        $booking->update([
            'status' => BookingStatus::Confirmed,
            'hold_expires_at' => null,
            'payment_plan' => $plan,
            'service_status' => $serviceStatus,
            'service_status_updated_at' => now()->subDays(fake()->numberBetween(0, 5)),
        ]);

        if ($verificationQueueCase) {
            $booking->update(['payment_status' => BookingPaymentStatus::PendingVerification]);
            $this->createPaymentsForBooking($booking, $plan, onlyFirstInstallment: true, admin: $admin, firstInstallmentUnmatched: true);

            return;
        }

        if ($plan === PaymentPlan::Full) {
            $booking->update(['payment_status' => BookingPaymentStatus::FullyPaid]);
            $this->createPaymentsForBooking($booking, $plan, onlyFirstInstallment: false, admin: $admin);
        } else {
            $booking->update(['payment_status' => BookingPaymentStatus::PartiallyPaid]);
            $this->createPaymentsForBooking($booking, $plan, onlyFirstInstallment: true, admin: $admin);
        }
    }

    private function applyCompleted(Booking $booking, PaymentPlan $plan, ?User $admin): void
    {
        $booking->update([
            'status' => BookingStatus::Completed,
            'hold_expires_at' => null,
            'payment_plan' => $plan,
            'payment_status' => BookingPaymentStatus::FullyPaid,
            'service_status' => ServiceTrackerStatus::Delivered,
            'service_status_updated_at' => now()->subDays(fake()->numberBetween(1, 90)),
        ]);

        $this->createPaymentsForBooking($booking, $plan, onlyFirstInstallment: false, admin: $admin);

        // ~80% of completed bookings get a review.
        if (fake()->boolean(80)) {
            $review = Review::factory()->create([
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'photographer_id' => $booking->photographer_id,
                'rating' => fake()->randomElement([5, 5, 5, 4, 4, 3, 2]),
            ]);

            if (fake()->boolean(45)) {
                $review->update([
                    'reply' => 'Thank you so much for the kind words — it was a pleasure working with you!',
                    'replied_at' => now()->subDays(fake()->numberBetween(0, 30)),
                ]);
            }

            if (fake()->boolean(8)) {
                $review->update([
                    'report_reason' => 'This review references a different booking than the one completed.',
                    'reported_at' => now()->subDays(fake()->numberBetween(0, 10)),
                ]);
            }
        }
    }

    private function createPaymentsForBooking(
        Booking $booking,
        PaymentPlan $plan,
        bool $onlyFirstInstallment,
        ?User $admin,
        bool $firstInstallmentUnmatched = false,
    ): void {
        $total = (float) $booking->total_price;
        $firstAmount = $plan === PaymentPlan::Full ? $total : round($total / 2, 2);
        $paymentDate = $booking->created_at?->format('Y-m-d') ?? now()->format('Y-m-d');

        if ($firstInstallmentUnmatched) {
            // Client submitted a GCash reference that doesn't (yet) match any
            // reference the photographer recorded — sits in the admin queue.
            Payment::factory()->create([
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'photographer_id' => $booking->photographer_id,
                'plan' => $plan,
                'amount' => $firstAmount,
                'photographer_payment_reference_id' => null,
                'matching_status' => fake()->randomElement(['pending_match', 'not_matched']),
                'payment_date' => $paymentDate,
            ]);

            return;
        }

        $firstReference = PhotographerPaymentReference::factory()->used()->create([
            'photographer_id' => $booking->photographer_id,
            'amount_received' => $firstAmount,
            'payment_date' => $paymentDate,
        ]);

        Payment::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $booking->client_id,
            'photographer_id' => $booking->photographer_id,
            'plan' => $plan,
            'amount' => $firstAmount,
            'reference_number' => $firstReference->reference_number,
            'photographer_payment_reference_id' => $firstReference->id,
            'payment_date' => $firstReference->payment_date,
            'matching_status' => 'matched',
            'verified_by' => $admin?->id,
            'verified_at' => now(),
            'verification_action' => 'verified',
        ]);

        if ($onlyFirstInstallment || $plan === PaymentPlan::Full) {
            return;
        }

        // Second (onsite) installment for Half-plan bookings that reached full payment.
        $onsiteFactory = Payment::factory()->onsite();
        $onsiteFactory = $admin ? $onsiteFactory->manuallyVerified($admin) : $onsiteFactory;

        $onsiteFactory->create([
            'booking_id' => $booking->id,
            'client_id' => $booking->client_id,
            'photographer_id' => $booking->photographer_id,
            'plan' => $plan,
            'amount' => round($total - $firstAmount, 2),
            'payment_date' => $booking->event_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'notes' => 'Remaining balance collected on-site.',
        ]);
    }

    /**
     * @param  Collection<int, User>  $clients
     * @param  Collection<int, array{user: User}>  $approved
     * @param  Collection<int, Booking>  $bookings
     */
    private function createReports(Collection $clients, Collection $approved, ?User $admin, Collection $bookings): void
    {
        $reportables = [
            ['target' => ReportTargetType::Booking, 'status' => ReportStatus::Resolved, 'severity' => ReportSeverity::High, 'action' => ReportRequestedAction::Refund],
            ['target' => ReportTargetType::Booking, 'status' => ReportStatus::UnderReview, 'severity' => ReportSeverity::Medium, 'action' => ReportRequestedAction::Investigate],
            ['target' => ReportTargetType::Booking, 'status' => ReportStatus::Submitted, 'severity' => ReportSeverity::Low, 'action' => ReportRequestedAction::Other],
            ['target' => ReportTargetType::Payment, 'status' => ReportStatus::UnderReview, 'severity' => ReportSeverity::Urgent, 'action' => ReportRequestedAction::Investigate],
            ['target' => ReportTargetType::Client, 'status' => ReportStatus::Closed, 'severity' => ReportSeverity::Medium, 'action' => ReportRequestedAction::Warn],
            ['target' => ReportTargetType::Studio, 'status' => ReportStatus::Resolved, 'severity' => ReportSeverity::High, 'action' => ReportRequestedAction::RemoveReview],
            ['target' => ReportTargetType::Bug, 'status' => ReportStatus::Submitted, 'severity' => ReportSeverity::Low, 'action' => ReportRequestedAction::Other],
            ['target' => ReportTargetType::Other, 'status' => ReportStatus::Submitted, 'severity' => ReportSeverity::Low, 'action' => ReportRequestedAction::Other],
        ];

        foreach ($reportables as $spec) {
            $reporter = fake()->boolean(60) ? $clients->random() : $approved->random()['user'];

            $referenceId = match ($spec['target']) {
                ReportTargetType::Booking => (string) $bookings->random()->id,
                ReportTargetType::Payment => (string) $bookings->random()->id,
                ReportTargetType::Client => (string) $clients->random()->id,
                ReportTargetType::Studio => (string) $approved->random()['user']->id,
                default => null,
            };

            $report = Report::factory()->create([
                'reporter_id' => $reporter->id,
                'target_type' => $spec['target']->value,
                'reference_id' => $referenceId,
                'reason' => match ($spec['target']) {
                    ReportTargetType::Booking => 'Photographer did not show up on the event date.',
                    ReportTargetType::Payment => 'GCash reference number was not recognized by the system.',
                    ReportTargetType::Client => 'Client was verbally abusive during the shoot.',
                    ReportTargetType::Studio => 'Delivered photos do not match the portfolio quality shown.',
                    ReportTargetType::Bug => 'Booking calendar shows the wrong available dates.',
                    default => 'General concern about platform behavior.',
                },
                'severity' => $spec['severity'],
                'details' => fake()->paragraph(),
                'requested_action' => $spec['action'],
                'status' => $spec['status'],
                'resolved_at' => in_array($spec['status'], [ReportStatus::Resolved, ReportStatus::Closed], true)
                    ? now()->subDays(fake()->numberBetween(0, 10))
                    : null,
            ]);

            if (in_array($spec['status'], [ReportStatus::UnderReview, ReportStatus::Resolved, ReportStatus::Closed], true)) {
                ReportNote::factory()->create([
                    'report_id' => $report->id,
                    'admin_id' => $admin?->id,
                    'note' => 'Reviewed the submitted evidence and reached out to both parties for clarification.',
                ]);

                if (fake()->boolean(40)) {
                    ReportNote::factory()->create([
                        'report_id' => $report->id,
                        'admin_id' => $admin?->id,
                        'note' => 'Follow-up: resolution communicated to the reporter.',
                    ]);
                }
            }
        }
    }

    /**
     * @param  Collection<int, array{user: User, application: PhotographerApplication}>  $photographers
     * @param  Collection<int, Booking>  $bookings
     */
    private function createActivityLogs(?User $admin, Collection $photographers, Collection $bookings): void
    {
        foreach ($photographers as $entry) {
            $status = $entry['application']->status;
            if ($status === PhotographerApplicationStatus::Draft) {
                continue;
            }

            [$action, $description] = match ($status) {
                PhotographerApplicationStatus::Approved => ['application.approved', 'Approved photographer application'],
                PhotographerApplicationStatus::Rejected => ['application.rejected', 'Rejected photographer application'],
                PhotographerApplicationStatus::RevisionRequested => ['application.revision_requested', 'Requested revisions on photographer application'],
                PhotographerApplicationStatus::PendingReview => ['application.submitted', 'Submitted photographer application for review'],
                default => ['application.updated', 'Updated photographer application'],
            };

            ActivityLog::create([
                'causer_id' => $status === PhotographerApplicationStatus::PendingReview ? $entry['user']->id : $admin?->id,
                'subject_type' => PhotographerApplication::class,
                'subject_id' => $entry['application']->id,
                'action' => $action,
                'description' => $description,
                'metadata' => ['photographer_name' => $entry['user']->name],
                'created_at' => now()->subDays(fake()->numberBetween(1, 60)),
            ]);
        }

        foreach ($bookings as $booking) {
            $status = $booking->status->value;
            if ($status === 'pending') {
                continue; // no state-change action yet
            }

            // 'accepted'/'rejected' as distinct statuses don't exist on the
            // real BookingStatus enum — both collapse into 'confirmed' and
            // 'cancelled' respectively. Use rejection_reason (set only by
            // RejectBookingAction) to tell a photographer rejection apart
            // from a client-initiated cancellation within the 'cancelled'
            // bucket, same signal the frontend would use.
            [$action, $description, $causerId] = match (true) {
                $status === 'confirmed' => ['booking.confirmed', 'Booking confirmed', $booking->photographer_id],
                $status === 'completed' => ['booking.completed', 'Service marked as completed', $booking->photographer_id],
                $status === 'cancelled' && $booking->rejection_reason !== null => ['booking.rejected', 'Photographer declined the booking request', $booking->photographer_id],
                $status === 'cancelled' => ['booking.cancelled', 'Booking was cancelled', $booking->client_id],
                $status === 'expired' => ['booking.expired', 'Booking hold expired without action', null],
                default => ['booking.updated', 'Booking updated', null],
            };

            ActivityLog::create([
                'causer_id' => $causerId,
                'subject_type' => Booking::class,
                'subject_id' => $booking->id,
                'action' => $action,
                'description' => $description,
                'metadata' => ['event_date' => $booking->event_date?->format('Y-m-d')],
                'created_at' => $booking->updated_at ?? now(),
            ]);

            $payments = Payment::where('booking_id', $booking->id)->get();

            foreach ($payments as $payment) {
                if (! in_array($payment->matching_status->value, ['matched', 'manually_verified'], true)) {
                    continue;
                }

                ActivityLog::create([
                    'causer_id' => $admin?->id,
                    'subject_type' => Payment::class,
                    'subject_id' => $payment->id,
                    'action' => 'payment.verified',
                    'description' => 'Verified payment against submitted reference',
                    'metadata' => ['amount' => (float) $payment->amount],
                    'created_at' => $payment->verified_at ?? $payment->created_at,
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, array{user: User}>  $approved
     */
    private function createProfileViews(Collection $approved): void
    {
        foreach ($approved as $entry) {
            $viewCount = fake()->numberBetween(8, 25);
            for ($i = 0; $i < $viewCount; $i++) {
                $viewedOn = now()->subDays(fake()->numberBetween(0, 30))->format('Y-m-d');

                ProfileView::create([
                    'photographer_id' => $entry['user']->id,
                    'viewer_hash' => hash('sha256', fake()->uuid()),
                    'viewed_on' => $viewedOn,
                    'created_at' => $viewedOn.' '.fake()->time(),
                ]);
            }
        }
    }

    private function createSearchLogs(): void
    {
        foreach (range(1, 30) as $i) {
            ServiceSearchLog::create([
                'term' => fake()->randomElement(self::SEARCH_TERMS),
                'created_at' => now()->subDays(fake()->numberBetween(0, 60)),
            ]);
        }
    }
}