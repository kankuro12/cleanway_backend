<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Team;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bootstrap 5 pagination markup (the admin shell loads Bootstrap via CDN).
        Paginator::useBootstrapFive();

        // Short morph aliases used by property/task assignment tables.
        Relation::enforceMorphMap([
            'user' => User::class,
            'team' => Team::class,
            'branch' => Branch::class,
        ]);

        // Runtime config overrides from the settings store (cached).
        try {
            app(SettingsService::class)->applyToConfig();
        } catch (\Throwable) {
            // Table may not exist during migrations.
        }
    }
}
