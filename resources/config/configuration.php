<?php

return [
    'service' => [
        'required' => true,
        'type'     => 'anomaly.field_type.select',
        'config'   => [
            'options' => [
                'anomaly.extension.ups_shipping_method::configuration.service.domestic'      => [
                    '03' => 'anomaly.extension.ups_shipping_method::configuration.service.option.03',
                    '12' => 'anomaly.extension.ups_shipping_method::configuration.service.option.12',
                    '13' => 'anomaly.extension.ups_shipping_method::configuration.service.option.13',
                    '14' => 'anomaly.extension.ups_shipping_method::configuration.service.option.14',
                    '59' => 'anomaly.extension.ups_shipping_method::configuration.service.option.59',
                ],
                'anomaly.extension.ups_shipping_method::configuration.service.international' => [
                    '70' => 'anomaly.extension.ups_shipping_method::configuration.service.option.70',
                    '01' => 'anomaly.extension.ups_shipping_method::configuration.service.option.01',
                    '65' => 'anomaly.extension.ups_shipping_method::configuration.service.option.65',
                    '11' => 'anomaly.extension.ups_shipping_method::configuration.service.option.11',
                    '02' => 'anomaly.extension.ups_shipping_method::configuration.service.option.02',
                    '07' => 'anomaly.extension.ups_shipping_method::configuration.service.option.07',
                    '54' => 'anomaly.extension.ups_shipping_method::configuration.service.option.54',
                    '08' => 'anomaly.extension.ups_shipping_method::configuration.service.option.08',
                ],
            ],
        ],
    ],
];
