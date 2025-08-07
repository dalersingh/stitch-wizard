<?php

use Illuminate\Support\Facades\Route;
use StitchWizard\Http\Controllers\WizardController;

Route::group([
    'prefix' => config('stitch-wizard.prefix', 'wizard'),
    'as' => 'stitch-wizard.',
], function () {
    Route::get('/{id}', [WizardController::class, 'show'])->name('show');
    Route::post('/{id}/step/{key}', [WizardController::class, 'postStep'])->name('step');
    Route::post('/{id}/finalize', [WizardController::class, 'finalize'])->name('finalize');
});
