<?php

return [
    'service' => [
        'required' => true,
        'type'     => 'anomaly.field_type.select',
        'config'   => [
            'options' => [
                'anomaly.extension.ups_shipping_method::configuration.service.domestic'      => [
                    '03' => 'UPS Ground',
                    '12' => 'UPS Three-Day Select',
                    '13' => 'Next Day Air Saver',
                    '14' => 'UPS Next Day Air Early AM',
                    '59' => 'UPS Second Day Air AM',
                ],
                'anomaly.extension.ups_shipping_method::configuration.service.international' => [
                    '70' => 'UPS Access Point Economy',
                    '01' => 'UPS Next Day Air',
                    '65' => 'UPS Saver',
                    '11' => 'UPS Standard',
                    '02' => 'UPS Second Day Air',
                    '07' => 'UPS Worldwide Express',
                    '54' => 'UPS Worldwide Express Plus',
                    '08' => 'UPS Worldwide Expedited',
                ],
            ],
        ],
    ],
];
