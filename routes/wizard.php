<?php

use Illuminate\Support\Facades\Route;
use StitchWizard\Http\Controllers\WizardController;

Route::group([
    'prefix' => config('stitch-wizard.prefix', 'wizard'),
    'as' => 'stitch-wizard.',
], function () {
    Route::get('/{id}', [WizardController::class, 'show'])->name('show');
    // Allow direct navigation / deep-linking to an individual step
    Route::get('/{id}/step/{key}', [WizardController::class, 'showStep'])->name('step.show');
    Route::post('/{id}/step/{key}', [WizardController::class, 'postStep'])->name('step');
    Route::post('/{id}/finalize', [WizardController::class, 'finalize'])->name('finalize');
});
