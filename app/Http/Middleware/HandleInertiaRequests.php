<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'                 => $user->id,
                    'name'               => $user->name,
                    'email'              => $user->email,
                    'avatar'             => $user->avatar,
                    'timezone'           => $user->timezone,
                    'utc_offset_minutes' => $user->utc_offset_minutes,
                    'email_verified_at'  => $user->email_verified_at,
                    'is_admin'           => $user->is_admin ?? false,
                    'is_super_admin'     => $user->is_super_admin ?? false,
                    'active_workspace_id' => $user->active_workspace_id,
                    'needs_upgrade'      => \App\Helpers\BillingHelper::needsUpgrade($user),
                    'trial_ends_at'      => $user->trial_ends_at,
                ] : null,
            ],
            'sidebarProjects' => $user ? \App\Models\Project::visibleTo($user)
                ->select('id', 'name', 'color', 'icon', 'archived_at', 'privacy')
                ->orderBy('name')
                ->limit(50)
                ->get()
                : [],
            
            // Pass translations to frontend (cached, single source of truth)
            'locale' => app()->getLocale(),
            'translations' => \App\Helpers\TranslationHelper::getSiteTranslations(),
        ]);
    }
}
