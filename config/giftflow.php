<?php

return [
    'webhook_url' => env('GIFTFLOW_WEBHOOK_URL'),

    'webhook_secret' => env('GIFTFLOW_WEBHOOK_SECRET'),

    'gift_cards_file' => 'giftcards.json',

    'sample_gift_cards' => [
        [
            'code' => 'GFLOW-TEST-0001',
            'status' => 'available',
            'product_id' => 'product_abc',
            'creator_id' => 'creator_123',
            'webhook' => [
                'status' => 'pending',
                'event_id' => null,
            ],
        ],
        [
            'code' => 'GFLOW-TEST-0002',
            'status' => 'available',
            'product_id' => 'product_def',
            'creator_id' => 'creator_123',
            'webhook' => [
                'status' => 'pending',
                'event_id' => null,
            ],
        ],
        [
            'code' => 'GFLOW-USED-0003',
            'status' => 'redeemed',
            'product_id' => 'product_ghi',
            'creator_id' => 'creator_456',
            'webhook' => [
                'status' => 'queued',
                'event_id' => 'evt_1234567890',
            ],
        ],
    ],
];
