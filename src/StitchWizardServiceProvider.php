<?php

namespace StitchWizard;

use Illuminate\Support\ServiceProvider;
use StitchWizard\Contracts\JsonSchemaValidator;
use StitchWizard\Contracts\VisibilityEngine;
use StitchWizard\Contracts\WizardStateStore;
use StitchWizard\Stores\SessionWizardStateStore;
use StitchWizard\Validation\BasicJsonSchemaValidator;
use StitchWizard\Visibility\SimpleVisibilityEngine;

class StitchWizardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/stitch-wizard.php',
            'stitch-wizard'
        );

        // Bind the state-store implementation defined in the package config (defaults
        // to the in-memory Session store) unless the application has provided its own.
        if (! $this->app->bound(WizardStateStore::class)) {
            $storeClass = config(
                'stitch-wizard.state_store',
                SessionWizardStateStore::class
            );

            $this->app->bind(WizardStateStore::class, $storeClass);
        }

        if (! $this->app->bound(JsonSchemaValidator::class)) {
            $this->app->bind(JsonSchemaValidator::class, BasicJsonSchemaValidator::class);
        }

        if (! $this->app->bound(VisibilityEngine::class)) {
            $this->app->bind(VisibilityEngine::class, SimpleVisibilityEngine::class);
        }
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/wizard.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'stitch-wizard');

        $this->publishes([
            __DIR__.'/../config/stitch-wizard.php' => config_path('stitch-wizard.php'),
        ], 'stitch-wizard-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/stitch-wizard'),
        ], 'stitch-wizard-views');

        // Package migrations (e.g., for the forthcoming database-backed state store)
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
