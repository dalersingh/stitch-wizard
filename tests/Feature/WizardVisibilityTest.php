<?php

it('hides annual income field for unemployed status', function () {
    $prefix = config('stitch-wizard.prefix', 'wizard');

    // First submit the basic step with valid data
    $response = $this->post("/$prefix/demo/step/basic", [
        'full_name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response->assertStatus(200);

    // Since the first step does not ask for income, ensure the field is not present yet
    $response->assertDontSee('Annual income')
        ->assertDontSee('income');
});

it('requires annual income field for employed status', function () {
    $prefix = config('stitch-wizard.prefix', 'wizard');

    // First submit the basic step with valid data
    $response = $this->post("/$prefix/demo/step/basic", [
        'full_name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $response->assertStatus(200);

    // Then submit the employment step with employed status but no income
    $response = $this->post("/$prefix/demo/step/employment", [
        'status' => 'employed',
    ]);

    // The response should contain the income field and validation error
    $response->assertStatus(200)
        ->assertSee('Annual income')
        ->assertSee('The income field is required');
});
