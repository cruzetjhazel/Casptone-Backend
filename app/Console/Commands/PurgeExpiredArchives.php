<?php

namespace App\Console\Commands;

use App\Models\AddOn;
use App\Models\Package;
use App\Models\PhotographerPortfolioImage;
use App\Models\WalkInClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeExpiredArchives extends Command
{
    protected $signature = 'archive:purge {--days=90 : Days a record may sit in the archive before permanent deletion}';

    protected $description = 'Permanently deletes archived packages, add-ons, portfolio images, and walk-in clients that have passed the retention window.';

    // Calculator tiers are intentionally excluded — there's no destroy
    // endpoint for them (restore-only), so nothing to purge.
    private const PURGEABLE_MODELS = [
        Package::class,
        AddOn::class,
        PhotographerPortfolioImage::class,
        WalkInClient::class,
    ];

    public function handle(): int
    {
        $cutoff = Carbon::now()->subDays((int) $this->option('days'));
        $deleted = 0;
        $skipped = 0;

        foreach (self::PURGEABLE_MODELS as $modelClass) {
            $expired = $modelClass::query()
                ->whereNotNull('archived_at')
                ->where('archived_at', '<=', $cutoff)
                ->get();

            foreach ($expired as $record) {
                try {
                    $record->delete();
                    $deleted++;
                } catch (\Throwable $e) {
                    // Records the app itself won't let us delete (e.g. still
                    // referenced by historical bookings) are left archived
                    // and retried on the next run rather than failing loudly.
                    $skipped++;
                    $this->warn("Could not purge {$modelClass}#{$record->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Purged {$deleted} expired archived record(s); skipped {$skipped}.");

        return self::SUCCESS;
    }
}