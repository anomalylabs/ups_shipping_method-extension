<?php

return [
    'service' => [
        'required' => true,
        'type'     => 'anomaly.field_type.select',
        'config'   => [
            'options' => \Ups\Entity\Service::getServices(),
        ],
    ],
];
