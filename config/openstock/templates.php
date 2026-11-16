<?php

return [
    'basic' => [
        'name' => 'Basic',
        'description' => 'A simple starting structure for small operations.',
        'locations' => [
            [
                'code' => 'warehouse',
                'name' => 'Warehouse',
                'type' => 'warehouse',
                'children' => [
                    [
                        'code' => 'receiving',
                        'name' => 'Receiving',
                        'type' => 'operational_area',
                    ],
                    [
                        'code' => 'storage',
                        'name' => 'Storage',
                        'type' => 'storage_area',
                    ],
                    [
                        'code' => 'shipping',
                        'name' => 'Shipping',
                        'type' => 'operational_area',
                    ],
                ],
            ],
        ],
    ],

    'standard' => [
        'name' => 'Standard',
        'description' => 'A more detailed structure for growing warehouse operations.',
        'locations' => [
            [
                'code' => 'warehouse',
                'name' => 'Warehouse',
                'type' => 'warehouse',
                'children' => [
                    [
                        'code' => 'receiving',
                        'name' => 'Receiving',
                        'type' => 'operational_area',
                    ],
                    [
                        'code' => 'storage',
                        'name' => 'Storage',
                        'type' => 'storage_area',
                        'children' => [
                            [
                                'code' => 'zone-a',
                                'name' => 'Zone A',
                                'type' => 'storage_area',
                                'children' => [
                                    [
                                        'code' => 'rack-a',
                                        'name' => 'Rack A',
                                        'type' => 'storage_area',
                                        'children' => [
                                            [
                                                'code' => 'shelf-a',
                                                'name' => 'Shelf A',
                                                'type' => 'storage_area',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'picking',
                        'name' => 'Picking',
                        'type' => 'operational_area',
                    ],
                    [
                        'code' => 'packing',
                        'name' => 'Packing',
                        'type' => 'operational_area',
                    ],
                    [
                        'code' => 'shipping',
                        'name' => 'Shipping',
                        'type' => 'operational_area',
                    ],
                    [
                        'code' => 'returns',
                        'name' => 'Returns',
                        'type' => 'operational_area',
                    ],
                ],
            ],
        ],
    ],
];
