<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\MarketplaceListing;
use App\Models\TradeRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $admin = auth()->user();
        $firstUser = User::oldest()->first();
        $actions = AdminAction::byAdmin($admin->id)->recent()->get();

        return view('admin.profile.index', [
            'admin' => $admin,
            'actions' => $actions,
            'stats' => [
                'users_monitored' => User::where('is_admin', false)->count(),
                'listings_reviewed' => AdminAction::byAdmin($admin->id)->where('target_type', 'listing')->count(),
                'reports_resolved' => AdminAction::byAdmin($admin->id)->whereIn('action_type', ['resolve_report', 'dismiss_report', 'verify_proof'])->count(),
                'platform_since' => $firstUser?->created_at?->diffForHumans() ?? 'today',
            ],
            'health' => [
                'active_users_week' => User::where('updated_at', '>=', now()->subWeek())->where('is_admin', false)->count(),
                'no_proof' => MarketplaceListing::where(function ($query) {
                    $query->whereNull('proof_of_ownership')->orWhere('proof_of_ownership', 'none');
                })->count(),
                'unresolved_reports' => MarketplaceListing::where('proof_status', 'pending')->whereNotNull('proof_photo')->count(),
                'trades_month' => TradeRequest::where('status', 'completed')->where('updated_at', '>=', now()->startOfMonth())->count(),
            ],
        ]);
    }
}
