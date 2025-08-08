<?php

namespace StitchWizard\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Additional setup can be added here
    }

    protected function getPackageProviders($app)
    {
        return [
            \StitchWizard\StitchWizardServiceProvider::class,
        ];
    }
}
