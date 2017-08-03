<?php

return [
    'access_key' => [
        'required' => true,
        'env'      => 'UPS_ACCESS_KEY',
        'bind'     => 'anomaly.extension.ups_shipping_method::config.access_key',
        'type'     => 'anomaly.field_type.encrypted',
    ],
    'username'   => [
        'required' => true,
        'env'      => 'UPS_USERNAME',
        'bind'     => 'anomaly.extension.ups_shipping_method::config.username',
        'type'     => 'anomaly.field_type.encrypted',
    ],
    'password'   => [
        'required' => true,
        'env'      => 'UPS_PASSWORD',
        'bind'     => 'anomaly.extension.ups_shipping_method::config.password',
        'type'     => 'anomaly.field_type.encrypted',
    ],
];
