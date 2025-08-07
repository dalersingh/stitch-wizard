<?php

it('can access the wizard start page', function () {
    $prefix = config('stitch-wizard.prefix', 'wizard');
    $response = $this->get("/$prefix/demo");
    
    $response->assertStatus(200);
});

it('can submit a wizard step', function () {
    $prefix = config('stitch-wizard.prefix', 'wizard');
    $response = $this->post("/$prefix/demo/step/basic", [
        'full_name' => 'Jane Doe',
        'email'     => 'jane@example.com',
    ]);
    
    $response->assertStatus(200)
             ->assertSee('Employment'); // next step title appears in rendered HTML
});
