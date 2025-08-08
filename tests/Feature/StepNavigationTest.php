<?php

it('can directly access a specific step', function () {
    $prefix = config('stitch-wizard.prefix', 'wizard');

    // Directly access the employment step
    $response = $this->get("/$prefix/demo/step/employment");

    // Assert the response is successful and contains the step title
    $response->assertStatus(200)
        ->assertSee('Employment');
});

it('shows correct step navigation information', function () {
    $prefix = config('stitch-wizard.prefix', 'wizard');

    // Access the employment step (which should be step 2)
    $response = $this->get("/$prefix/demo/step/employment");

    // Assert the response contains step navigation information
    $response->assertStatus(200)
        ->assertSee('Step 2 of 2')
        ->assertSee('Back');
});
