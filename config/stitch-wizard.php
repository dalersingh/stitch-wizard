<?php

return [
    'prefix' => env('STITCH_WIZARD_PREFIX', 'wizard'),
    'state_store' => \StitchWizard\Stores\SessionWizardStateStore::class,

    'wizards' => [
        'demo' => [
            'title' => 'Demo Wizard',
            'steps' => [
                [
                    'key' => 'basic',
                    'title' => 'Basic',
                    'fields' => [
                        [
                            'key' => 'full_name',
                            'label' => 'Full name',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'min:2'],
                        ],
                        [
                            'key' => 'email',
                            'label' => 'Email',
                            'type' => 'text',
                            'rules' => ['required', 'email'],
                        ],
                    ],
                ],
                [
                    'key' => 'employment',
                    'title' => 'Employment',
                    'fields' => [
                        [
                            'key' => 'status',
                            'label' => 'Employment status',
                            'type' => 'select',
                            'options' => [
                                ['value' => 'employed',   'label' => 'Employed'],
                                ['value' => 'self',       'label' => 'Self-employed'],
                                ['value' => 'unemployed', 'label' => 'Unemployed'],
                            ],
                            'rules' => ['required', 'in:employed,self,unemployed'],
                        ],
                        [
                            'key' => 'income',
                            'label' => 'Annual income',
                            'type' => 'number',
                            'rules' => ['required', 'numeric', 'min:0'],
                            'visibility' => [
                                'logic' => 'all',
                                'rules' => [
                                    [
                                        'path' => 'status',
                                        'op' => 'in',
                                        'value' => ['employed', 'self'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'real-estate' => [
            'title' => 'Real Estate Listing',
            'steps' => [
                [
                    'key' => 'basics',
                    'title' => 'Basic Information',
                    'fields' => [
                        [
                            'key' => 'listing_type',
                            'label' => 'Listing Type',
                            'type' => 'radio',
                            'options' => [
                                ['value' => 'sell', 'label' => 'Sell'],
                                ['value' => 'rent', 'label' => 'Rent'],
                            ],
                            'rules' => ['required', 'in:sell,rent'],
                        ],
                        [
                            'key' => 'property_type',
                            'label' => 'Property Type',
                            'type' => 'select',
                            'options' => [
                                ['value' => 'condo', 'label' => 'Condominium'],
                                ['value' => 'house', 'label' => 'House'],
                                ['value' => 'townhouse', 'label' => 'Townhouse'],
                                ['value' => 'land', 'label' => 'Land'],
                                ['value' => 'commercial', 'label' => 'Commercial'],
                            ],
                            'rules' => ['required', 'in:condo,house,townhouse,land,commercial'],
                        ],
                        [
                            'key' => 'project_name',
                            'label' => 'Project Name',
                            'type' => 'text',
                            'rules' => ['nullable'],
                        ],
                        [
                            'key' => 'title',
                            'label' => 'Title',
                            'type' => 'text',
                            'rules' => ['required'],
                        ],
                        [
                            'key' => 'description',
                            'label' => 'Description',
                            'type' => 'textarea',
                            'rules' => ['required', 'min:20'],
                        ],
                        [
                            'key' => 'price',
                            'label' => 'Price',
                            'type' => 'number',
                            'rules' => ['required_if:listing_type,sell', 'numeric', 'min:0'],
                            'visibility' => [
                                'logic' => 'all',
                                'rules' => [
                                    [
                                        'path' => 'listing_type',
                                        'op' => '=',
                                        'value' => 'sell',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'rent_price',
                            'label' => 'Rent Price',
                            'type' => 'number',
                            'rules' => ['required_if:listing_type,rent', 'numeric', 'min:0'],
                            'visibility' => [
                                'logic' => 'all',
                                'rules' => [
                                    [
                                        'path' => 'listing_type',
                                        'op' => '=',
                                        'value' => 'rent',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'currency',
                            'label' => 'Currency',
                            'type' => 'select',
                            'options' => [
                                ['value' => 'THB', 'label' => 'Thai Baht (THB)'],
                                ['value' => 'USD', 'label' => 'US Dollar (USD)'],
                            ],
                            'rules' => ['required', 'in:THB,USD'],
                        ],
                        [
                            'key' => 'negotiable',
                            'label' => 'Price Negotiable',
                            'type' => 'toggle',
                            'rules' => ['boolean'],
                        ],
                    ],
                ],
                [
                    'key' => 'location',
                    'title' => 'Location',
                    'fields' => [
                        [
                            'key' => 'province',
                            'label' => 'Province',
                            'type' => 'text',
                            'rules' => ['required'],
                        ],
                        [
                            'key' => 'district',
                            'label' => 'District',
                            'type' => 'text',
                            'rules' => ['required'],
                        ],
                        [
                            'key' => 'subdistrict',
                            'label' => 'Subdistrict',
                            'type' => 'text',
                            'rules' => [],
                        ],
                        [
                            'key' => 'address_line',
                            'label' => 'Address Line',
                            'type' => 'text',
                            'rules' => [],
                        ],
                        [
                            'key' => 'latitude',
                            'label' => 'Latitude',
                            'type' => 'text',
                            'rules' => [],
                        ],
                        [
                            'key' => 'longitude',
                            'label' => 'Longitude',
                            'type' => 'text',
                            'rules' => [],
                        ],
                        [
                            'key' => 'transit_line',
                            'label' => 'Transit Line',
                            'type' => 'select',
                            'options' => [
                                ['value' => 'BTS', 'label' => 'BTS Skytrain'],
                                ['value' => 'MRT', 'label' => 'MRT Subway'],
                                ['value' => 'ARL', 'label' => 'Airport Rail Link'],
                                ['value' => 'None', 'label' => 'None'],
                            ],
                            'rules' => ['in:BTS,MRT,ARL,None'],
                        ],
                        [
                            'key' => 'transit_station',
                            'label' => 'Transit Station',
                            'type' => 'text',
                            'rules' => ['nullable'],
                        ],
                        [
                            'key' => 'transit_distance_m',
                            'label' => 'Distance to Transit (meters)',
                            'type' => 'number',
                            'rules' => ['nullable', 'min:0'],
                        ],
                    ],
                ],
                [
                    'key' => 'details',
                    'title' => 'Property Details',
                    'fields' => [
                        [
                            'key' => 'bedrooms',
                            'label' => 'Bedrooms',
                            'type' => 'number',
                            'rules' => ['min:0'],
                        ],
                        [
                            'key' => 'bathrooms',
                            'label' => 'Bathrooms',
                            'type' => 'number',
                            'rules' => ['min:0'],
                        ],
                        [
                            'key' => 'floor',
                            'label' => 'Floor',
                            'type' => 'number',
                            'rules' => ['min:0'],
                        ],
                        [
                            'key' => 'total_floors',
                            'label' => 'Total Floors',
                            'type' => 'number',
                            'rules' => ['min:0'],
                        ],
                        [
                            'key' => 'usable_area_sqm',
                            'label' => 'Usable Area (sqm)',
                            'type' => 'number',
                            'rules' => ['required', 'min:0'],
                        ],
                        [
                            'key' => 'land_size_rai',
                            'label' => 'Land Size (Rai)',
                            'type' => 'number',
                            'rules' => ['min:0'],
                        ],
                        [
                            'key' => 'land_size_ngan',
                            'label' => 'Land Size (Ngan)',
                            'type' => 'number',
                            'rules' => ['min:0'],
                        ],
                        [
                            'key' => 'land_size_wah',
                            'label' => 'Land Size (Wah)',
                            'type' => 'number',
                            'rules' => ['min:0'],
                        ],
                        [
                            'key' => 'furnishing',
                            'label' => 'Furnishing',
                            'type' => 'select',
                            'options' => [
                                ['value' => 'none', 'label' => 'None'],
                                ['value' => 'partial', 'label' => 'Partially Furnished'],
                                ['value' => 'full', 'label' => 'Fully Furnished'],
                            ],
                            'rules' => ['in:none,partial,full'],
                        ],
                        [
                            'key' => 'ownership',
                            'label' => 'Ownership',
                            'type' => 'select',
                            'options' => [
                                ['value' => 'freehold', 'label' => 'Freehold'],
                                ['value' => 'leasehold', 'label' => 'Leasehold'],
                            ],
                            'rules' => ['in:freehold,leasehold'],
                        ],
                        [
                            'key' => 'title_deed',
                            'label' => 'Title Deed',
                            'type' => 'select',
                            'options' => [
                                ['value' => 'chanote', 'label' => 'Chanote'],
                                ['value' => 'nor_sor_3_gor', 'label' => 'Nor Sor 3 Gor'],
                                ['value' => 'nor_sor_3', 'label' => 'Nor Sor 3'],
                                ['value' => 'por_bor_tor_5', 'label' => 'Por Bor Tor 5'],
                            ],
                            'rules' => ['in:chanote,nor_sor_3_gor,nor_sor_3,por_bor_tor_5'],
                        ],
                        [
                            'key' => 'year_built',
                            'label' => 'Year Built',
                            'type' => 'number',
                            'rules' => ['nullable', 'integer', 'min:1800', 'max:2100'],
                        ],
                        [
                            'key' => 'facing',
                            'label' => 'Facing',
                            'type' => 'select',
                            'options' => [
                                ['value' => 'north', 'label' => 'North'],
                                ['value' => 'south', 'label' => 'South'],
                                ['value' => 'east', 'label' => 'East'],
                                ['value' => 'west', 'label' => 'West'],
                            ],
                            'rules' => ['in:north,south,east,west'],
                        ],
                    ],
                ],
                [
                    'key' => 'features',
                    'title' => 'Features',
                    'fields' => [
                        [
                            'key' => 'facilities',
                            'label' => 'Facilities',
                            'type' => 'multiselect',
                            'options' => [
                                ['value' => 'pool', 'label' => 'Swimming Pool'],
                                ['value' => 'gym', 'label' => 'Gym'],
                                ['value' => 'parking', 'label' => 'Parking'],
                                ['value' => 'security', 'label' => 'Security'],
                                ['value' => 'cctv', 'label' => 'CCTV'],
                                ['value' => 'sauna', 'label' => 'Sauna'],
                                ['value' => 'garden', 'label' => 'Garden'],
                                ['value' => 'playground', 'label' => 'Playground'],
                                ['value' => 'elevator', 'label' => 'Elevator'],
                            ],
                            'rules' => [],
                        ],
                        [
                            'key' => 'pet_friendly',
                            'label' => 'Pet Friendly',
                            'type' => 'toggle',
                            'rules' => [],
                        ],
                        [
                            'key' => 'balcony',
                            'label' => 'Balcony',
                            'type' => 'toggle',
                            'rules' => [],
                        ],
                        [
                            'key' => 'air_conditioning_units',
                            'label' => 'Air Conditioning Units',
                            'type' => 'number',
                            'rules' => ['min:0'],
                        ],
                        [
                            'key' => 'kitchen_built_in',
                            'label' => 'Built-in Kitchen',
                            'type' => 'toggle',
                            'rules' => [],
                        ],
                        [
                            'key' => 'appliances',
                            'label' => 'Appliances',
                            'type' => 'multiselect',
                            'options' => [
                                ['value' => 'fridge', 'label' => 'Refrigerator'],
                                ['value' => 'washer', 'label' => 'Washing Machine'],
                                ['value' => 'dryer', 'label' => 'Dryer'],
                                ['value' => 'oven', 'label' => 'Oven'],
                                ['value' => 'stove', 'label' => 'Stove'],
                                ['value' => 'hood', 'label' => 'Hood'],
                                ['value' => 'microwave', 'label' => 'Microwave'],
                                ['value' => 'water_heater', 'label' => 'Water Heater'],
                                ['value' => 'television', 'label' => 'Television'],
                            ],
                            'rules' => [],
                        ],
                    ],
                ],
                [
                    'key' => 'media',
                    'title' => 'Media',
                    'fields' => [
                        [
                            'key' => 'cover_photo',
                            'label' => 'Cover Photo',
                            'type' => 'file',
                            'rules' => ['required'],
                        ],
                        [
                            'key' => 'photos',
                            'label' => 'Additional Photos',
                            'type' => 'file',
                            'rules' => [],
                        ],
                        [
                            'key' => 'video_url',
                            'label' => 'Video URL',
                            'type' => 'text',
                            'rules' => ['nullable', 'url'],
                        ],
                        [
                            'key' => 'floor_plan',
                            'label' => 'Floor Plan',
                            'type' => 'file',
                            'rules' => [],
                        ],
                    ],
                ],
                [
                    'key' => 'contact',
                    'title' => 'Contact Information',
                    'fields' => [
                        [
                            'key' => 'contact_name',
                            'label' => 'Contact Name',
                            'type' => 'text',
                            'rules' => ['required'],
                        ],
                        [
                            'key' => 'phone',
                            'label' => 'Phone Number',
                            'type' => 'text',
                            'rules' => ['required'],
                        ],
                        [
                            'key' => 'email',
                            'label' => 'Email',
                            'type' => 'text',
                            'rules' => ['required', 'email'],
                        ],
                        [
                            'key' => 'agency_name',
                            'label' => 'Agency Name',
                            'type' => 'text',
                            'rules' => [],
                        ],
                        [
                            'key' => 'agent_license',
                            'label' => 'Agent License Number',
                            'type' => 'text',
                            'rules' => [],
                        ],
                        [
                            'key' => 'publish_now',
                            'label' => 'Publish Immediately',
                            'type' => 'toggle',
                            'rules' => [],
                        ],
                        [
                            'key' => 'agree_terms',
                            'label' => 'I agree to the terms and conditions',
                            'type' => 'checkbox',
                            'rules' => ['required', 'accepted'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
