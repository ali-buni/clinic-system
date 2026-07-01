<?php

return [
    'appointment_pool' => [
        'completed' => 430,
        'scheduled' => 25,
        'cancelled' => 20,
        'no_show' => 15,
        'confirmed' => 10,
    ],
    'invoice_map' => [
        'completed' => 'paid',
        'scheduled' => 'draft',
        'cancelled' => 'void',
        'no_show' => 'refunded',
        'confirmed' => 'issued',
    ],
];
