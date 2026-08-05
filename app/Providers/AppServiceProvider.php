<?php

namespace App\Providers;

use App\Mail\FileMailTransport;
use App\Models\Branch;
use App\Models\Team;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Mail\MailManager;
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

        // Custom dev mail transport: writes mails to files instead of the log.
        app(MailManager::class)->extend('file', function (array $config) {
            return new FileMailTransport($config['path'] ?? storage_path('app/private/mails'));
        });

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
