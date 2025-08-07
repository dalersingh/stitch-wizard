<?php

it('can finalize a wizard via AJAX', function () {
    $prefix = config('stitch-wizard.prefix', 'wizard');
    $wizardId = 'demo';
    
    $response = $this->post("/$prefix/$wizardId/finalize");
    
    $response->assertStatus(200)
             ->assertSee('Wizard Completed');
});
