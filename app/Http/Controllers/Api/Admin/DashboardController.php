<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\AccountType;
use App\Enums\PhotographerApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PhotographerApplication;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponses;

    /**
     * GET /admin/dashboard-stats
     * Single-call summary for the admin dashboard's stat cards.
     */
    public function stats(Request $request)
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $totalUsers = User::count();

        $activeClients = User::where('account_type', AccountType::Client)
            ->where('account_status', 'active')
            ->count();

        $professionals = User::where('account_type', AccountType::Photographer)
            ->where('account_status', 'active')
            ->whereHas('photographerApplication', fn ($q) => $q->where('status', PhotographerApplicationStatus::Approved))
            ->count();

        $totalBookings = Booking::count();

        $pendingReviews = PhotographerApplication::where('status', PhotographerApplicationStatus::PendingReview)->count();

        // --- Analytics deltas ---
        // TODO: confirm against real Activity Log action names / PhotographerApplication
        // timestamp column before trusting these numbers — see note below.
        return $this->success([
            'total_users' => $totalUsers,
            'active_clients' => $activeClients,
            'professionals' => $professionals,
            'total_bookings' => $totalBookings,
            'pending_reviews' => $pendingReviews,
            'analytics' => [
                // placeholder shape — filled in once action names / date column confirmed
            ],
        ]);
    }
}