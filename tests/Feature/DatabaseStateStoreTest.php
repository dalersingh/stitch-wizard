<?php

use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Configure SQLite in-memory database
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ]);

    // Use the database state store
    config([
        'stitch-wizard.state_store' => \StitchWizard\Stores\DatabaseWizardStateStore::class,
    ]);

    // Explicitly bind the contract to the database-backed implementation for the container
    app()->bind(
        \StitchWizard\Contracts\WizardStateStore::class,
        \StitchWizard\Stores\DatabaseWizardStateStore::class
    );

    // Run migrations to create the necessary tables
    $this->artisan('migrate');
});

it('can persist wizard state in database', function () {
    $prefix = config('stitch-wizard.prefix', 'wizard');

    // Submit valid data to the first step
    $response = $this->withSession([])->post("/$prefix/demo/step/basic", [
        'full_name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    // Assert the response is successful
    $response->assertStatus(200);

    // Assert that the state was persisted to the database
    expect(DB::table('wizard_states')->count())->toBe(1);
});

it('clears state on finalize', function () {
    $prefix = config('stitch-wizard.prefix', 'wizard');

    // Submit valid data to the first step
    $this->withSession([])->post("/$prefix/demo/step/basic", [
        'full_name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    // Submit valid data to the second step
    $this->post("/$prefix/demo/step/employment", [
        'status' => 'unemployed',
    ]);

    // Finalize the wizard
    $this->post("/$prefix/demo/finalize");

    // Assert that the state was cleared from the database
    expect(DB::table('wizard_states')->count())->toBe(0);
});
