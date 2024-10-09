<?php

dataset('payment cases', [
    'debit card' => [
        [
            'currency' => 'VED',
            'amount' => 1,
            'reason' => 'PRUEBA',
            'country' => 'VE',
            'payer_name' => 'DHARRYLX',
            'debit_card' => [
                'holder_name' => 'DHARRYLX',
                'holder_id' => 'V016673906',
                'holder_id_doc' => 'RIF',
                'card_number' => '5859480000000146871',
                'cvc' => '941',
                'expiration_month' => 6,
                'expiration_year' => 24,
                'card_type' => 'DEBIT',
                'account_type' => 'CORRIENTE',
                'pin' => 'pin_value',
            ],
        ],
        [
            'financial_card_emitter' => ['name' => 'VISA', 'thumbnail' => 'path/to/image.ext'],
            'card_status' => 'LOCKED',
            'otp_ccr_config' => [
                'enabled' => true,
                'code_min_integer' => 15,
                'code_max_integer' => 40,
                'code_expiration_time' => 24,
                'code_expiration_time_unit' => 'HOURS',
                'validation_expires_at_time' => 180,
                'validation_expires_at_time_unit' => 'DAYS',
            ],
        ],
    ],
    'credit card' => [
        [
            'currency' => 'VED',
            'amount' => 1,
            'reason' => 'PRUEBA',
            'country' => 'VE',
            'payer_name' => 'DHARRYLX',
            'credit_card' => [
                'holder_name' => 'DHARRYLX',
                'holder_id' => 'V004000004',
                'holder_id_doc' => 'RIF',
                'card_number' => '4222610122997125',
                'cvc' => '808',
                'expiration_month' => 12,
                'expiration_year' => 24,
                'card_type' => 'CREDIT',
            ],
        ],
        [
            'code' => 202,
            'message' => 'CREDICARD_RESPONSE_UNSUCCESSFUL',
            'cause' => ['05', 'NEGADA'],
        ],
    ],
    'international credit card' => [
        [
            'currency' => 'USD',
            'amount' => 10,
            'reason' => 'PRUEBA',
            'payer_name' => 'DHARRYLX',
            'credit_card' => [
                'card_number' => '4222610122997125',
                'expiration_month' => 12,
                'expiration_year' => 24,
                'holder_name' => 'dharryl rodriguez',
                'holder_id_doc' => 'CI',
                'holder_id' => 'V004000004',
                'card_type' => 'CREDIT',
                'cvc' => '808',
                'currency' => 'USD',
                'bank_card_validation' => ['token' => '072', 'rif' => 'V004000004'],
            ],
        ],
        [
            'code' => 202,
            'message' => 'CREDICARD_RESPONSE_UNSUCCESSFUL',
            'cause' => ['05', 'NEGADA'],
        ],
    ],
]);
