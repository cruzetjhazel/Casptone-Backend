<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\PhotographerApplicationStatus;
use App\Enums\PhotographerType;
use App\Models\PhotographerApplication;
use App\Models\PhotographerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Seeds 6 specific, named photographer/studio providers with complete
 * profiles and real (user-supplied) profile/cover images — separate from
 * DemoSeeder::createPhotographers(), which generates random showcase data
 * and should not be touched by this seeder.
 *
 * Image source files are expected at database/seeders/images/, named
 * "{slug}-logo.{ext}" and "{slug}-cover.{ext}" (jpg/jpeg/png/webp), and are
 * copied into the existing 'public' storage disk under
 * photographers/seed/{slug}/ — the same disk PhotographerProfileResource
 * already builds URLs from via Storage::disk('public')->url(...).
 *
 * Only runs in local/testing, same guard as DemoSeeder. Run after
 * AdminSeeder so an administrator exists to attribute the application
 * review to (matches how DemoSeeder does it).
 */
class PhotographerShowcaseSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     slug: string, name: string, type: PhotographerType, location: string,
     *     bio: string, style: array<int, string>, email: string, phone: string,
     *     years_active: int, team_size: int|null, services: array<int, string>,
     *     shooting_types: array<int, string>, price_min: float, price_max: float,
     *     coverage_area: string, facebook: string|null, instagram: string|null,
     *     website: string|null,
     * }>
     */
    private const PROVIDERS = [
        [
            'slug' => 'hh-production',
            'name' => 'HHProduction',
            'type' => PhotographerType::Studio,
            'location' => 'Bulan, Sorsogon',
            'bio' => 'HHProduction is a full-service event photography and videography studio based in Bulan, Sorsogon. Our team specializes in weddings, debuts, and corporate events, blending cinematic storytelling with candid, unscripted moments.',
            'style' => ['Cinematic', 'Documentary'],
            'email' => 'contact@hhproduction.test',
            'phone' => '09171234501',
            'years_active' => 7,
            'team_size' => 5,
            'services' => ['Wedding', 'Corporate', 'Videography'],
            'shooting_types' => ['indoor', 'outdoor'],
            'price_min' => 12000,
            'price_max' => 45000,
            'coverage_area' => 'sorsogon_wide',
            'facebook' => 'https://facebook.com/hhproduction.studio',
            'instagram' => 'https://instagram.com/hhproduction.studio',
            'website' => null,
        ],
        [
            'slug' => 'kap-studio',
            'name' => 'KAP Studio',
            'type' => PhotographerType::Studio,
            'location' => 'Bulan, Sorsogon',
            'bio' => 'KAP Studio is a boutique photography studio known for clean, fine-art portraiture and elegant wedding coverage. We work closely with every couple and family to design a shoot that feels personal, not templated.',
            'style' => ['Fine Art', 'Traditional'],
            'email' => 'hello@kapstudio.test',
            'phone' => '09171234502',
            'years_active' => 5,
            'team_size' => 3,
            'services' => ['Wedding', 'Portrait', 'Debut'],
            'shooting_types' => ['indoor', 'outdoor'],
            'price_min' => 8000,
            'price_max' => 30000,
            'coverage_area' => 'bulan_only',
            'facebook' => 'https://facebook.com/kapstudio.ph',
            'instagram' => 'https://instagram.com/kapstudio.ph',
            'website' => null,
        ],
        [
            'slug' => 'amaras-studio',
            'name' => 'Amaras Studio',
            'type' => PhotographerType::Studio,
            'location' => 'Bulan, Sorsogon',
            'bio' => 'Amaras Studio brings a warm, romantic aesthetic to weddings, prenups, and family portraits. Our small team is hands-on from initial consultation through final gallery delivery, so every shoot gets full creative attention.',
            'style' => ['Traditional', 'Candid'],
            'email' => 'bookings@amarasstudio.test',
            'phone' => '09171234503',
            'years_active' => 4,
            'team_size' => 4,
            'services' => ['Wedding', 'Portrait', 'Family'],
            'shooting_types' => ['indoor', 'outdoor'],
            'price_min' => 9000,
            'price_max' => 32000,
            'coverage_area' => 'sorsogon_wide',
            'facebook' => 'https://facebook.com/amarasstudio',
            'instagram' => null,
            'website' => 'https://amarasstudio.test',
        ],
        [
            'slug' => 'cj-creatives',
            'name' => 'CJ Creatives',
            'type' => PhotographerType::Freelancer,
            'location' => 'Bulan, Sorsogon',
            'bio' => 'CJ Creatives is a freelance photographer focused on candid, story-driven coverage for birthdays, debuts, and small intimate events. Bringing an easygoing, unobtrusive shooting style so subjects stay relaxed and natural.',
            'style' => ['Candid', 'Documentary'],
            'email' => 'cj.creatives@example.test',
            'phone' => '09171234504',
            'years_active' => 3,
            'team_size' => null,
            'services' => ['Birthday', 'Debut', 'Portrait'],
            'shooting_types' => ['indoor', 'outdoor'],
            'price_min' => 3000,
            'price_max' => 12000,
            'coverage_area' => 'bulan_only',
            'facebook' => 'https://facebook.com/cjcreatives.ph',
            'instagram' => 'https://instagram.com/cj.creatives',
            'website' => null,
        ],
        [
            'slug' => 'joesol-photography',
            'name' => 'Joesol Photography',
            'type' => PhotographerType::Freelancer,
            'location' => 'Bulan, Sorsogon',
            'bio' => 'Joesol Photography specializes in graduation, christening, and corporate event coverage, with a focus on clean, well-lit, dependable delivery — the go-to choice for clients who want their event documented without drama.',
            'style' => ['Traditional', 'Documentary'],
            'email' => 'joesol.photography@example.test',
            'phone' => '09171234505',
            'years_active' => 6,
            'team_size' => null,
            'services' => ['Graduation', 'Christening', 'Corporate'],
            'shooting_types' => ['indoor', 'outdoor'],
            'price_min' => 3500,
            'price_max' => 15000,
            'coverage_area' => 'sorsogon_wide',
            'facebook' => 'https://facebook.com/joesolphotography',
            'instagram' => null,
            'website' => null,
        ],
        [
            'slug' => 'frederick-robelas',
            'name' => 'Frederick Robelas',
            'type' => PhotographerType::Freelancer,
            'location' => 'Bulan, Sorsogon',
            'bio' => 'Frederick Robelas is a freelance photographer with a passion for outdoor prenup and portrait sessions, combining natural light with fine-art composition to create timeless, editorial-style images.',
            'style' => ['Fine Art', 'Cinematic'],
            'email' => 'frederick.robelas@example.test',
            'phone' => '09171234506',
            'years_active' => 2,
            'team_size' => null,
            'services' => ['Prenup', 'Portrait'],
            'shooting_types' => ['outdoor'],
            'price_min' => 4000,
            'price_max' => 14000,
            'coverage_area' => 'bulan_only',
            'facebook' => 'https://facebook.com/fred.robelas.photo',
            'instagram' => 'https://instagram.com/fred.robelas',
            'website' => null,
        ],
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('PhotographerShowcaseSeeder only runs in local/testing.');

            return;
        }

        $reviewer = User::where('account_type', AccountType::Administrator)->first();

        foreach (self::PROVIDERS as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone_number' => $data['phone'],
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'),
                    'account_type' => AccountType::Photographer,
                ]
            );

            PhotographerApplication::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'photographer_type' => $data['type'],
                    'status' => PhotographerApplicationStatus::Approved,
                    'business_name' => $data['name'],
                    'location' => $data['location'],
                    'years_active' => $data['years_active'],
                    'team_size' => $data['team_size'],
                    'services' => $data['services'],
                    'coverage_area' => $data['coverage_area'],
                    'shooting_types' => $data['shooting_types'],
                    'price_min' => $data['price_min'],
                    'price_max' => $data['price_max'],
                    'submitted_at' => now(),
                    'reviewed_at' => now(),
                    'reviewed_by' => $reviewer?->id,
                ]
            );

            $profilePhotoPath = $this->seedImage($data['slug'], 'logo', 'profile');
            $coverPhotoPath = $this->seedImage($data['slug'], 'cover', 'cover');

            PhotographerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => $data['bio'],
                    'style' => $data['style'],
                    'profile_photo_path' => $profilePhotoPath,
                    'cover_photo_path' => $coverPhotoPath,
                    'facebook' => $data['facebook'],
                    'instagram' => $data['instagram'],
                    'website' => $data['website'],
                ]
            );

            $this->command->info("Seeded showcase provider: {$data['name']} ({$data['type']->value})");
        }
    }

    /**
     * Copies database/seeders/images/{slug}-{kind}.{ext} into the 'public'
     * disk (the same disk PhotographerProfileResource reads from) and
     * returns the stored relative path, or null if no matching source file
     * was found — so a partial set of images doesn't crash the whole run.
     */
    private function seedImage(string $slug, string $kind, string $storedAs): ?string
    {
        $sourceDir = database_path('seeders/images');

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
            $sourceFile = "{$sourceDir}/{$slug}-{$kind}.{$extension}";

            if (! is_file($sourceFile)) {
                continue;
            }

            $destPath = "photographers/seed/{$slug}/{$storedAs}.{$extension}";
            Storage::disk('public')->put($destPath, file_get_contents($sourceFile));

            return $destPath;
        }

        $this->command->warn(
            "No {$kind} image found for '{$slug}' — expected database/seeders/images/{$slug}-{$kind}.{jpg,jpeg,png,webp}. ".
            'Leaving that field null; re-run this seeder once the file is in place.'
        );

        return null;
    }
}