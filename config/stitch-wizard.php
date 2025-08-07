<?php

return [
    'prefix' => env('STITCH_WIZARD_PREFIX', 'wizard'),
    'state_store' => \StitchWizard\Stores\SessionWizardStateStore::class,

    /*
    |--------------------------------------------------------------------------
    | Wizard Definitions (Sample)
    |--------------------------------------------------------------------------
    |
    | A minimal “demo” wizard used by tests and local examples. Real projects
    | are expected to publish this config and extend / replace the definitions.
    |
    */

    'wizards' => [
        'demo' => [
            'title' => 'Demo Wizard',
            'steps' => [
                [
                    'key' => 'basic',
                    'title' => 'Basic',
                    'fields' => [
                        [
                            'key'   => 'full_name',
                            'label' => 'Full name',
                            'type'  => 'text',
                            'rules' => ['required', 'string', 'min:2'],
                        ],
                        [
                            'key'   => 'email',
                            'label' => 'Email',
                            'type'  => 'text',
                            'rules' => ['required', 'email'],
                        ],
                    ],
                ],
                [
                    'key' => 'employment',
                    'title' => 'Employment',
                    'fields' => [
                        [
                            'key'   => 'status',
                            'label' => 'Employment status',
                            'type'  => 'select',
                            'options' => [
                                ['value' => 'employed',   'label' => 'Employed'],
                                ['value' => 'self',       'label' => 'Self-employed'],
                                ['value' => 'unemployed', 'label' => 'Unemployed'],
                            ],
                            'rules' => ['required', 'in:employed,self,unemployed'],
                        ],
                        [
                            'key'   => 'income',
                            'label' => 'Annual income',
                            'type'  => 'number',
                            'rules' => ['required', 'numeric', 'min:0'],
                            'visibility' => [
                                'logic' => 'all',
                                'rules' => [
                                    [
                                        'path'  => 'status',
                                        'op'    => 'in',
                                        'value' => ['employed', 'self'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
