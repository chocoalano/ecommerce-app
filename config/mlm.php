<?php

return [
    // Status order yang memicu komisi
    'trigger_status' => env('MLM_TRIGGER_STATUS', 'paid'), // mis: paid|completed

    // Dari mana nilai dasar komisi diambil
    // grand_total|subtotal|paid_amount
    'base_amount_source' => env('MLM_BASE_SOURCE', 'grand_total'),

    // Persentase sponsor langsung (level 1)
    'sponsor_percent' => (float) env('MLM_SPONSOR_PERCENT', 5.0),

    // Matching: berapa level dan persen per level (csv)
    // contoh: 3 level -> "3,2,1" artinya L1=3%, L2=2%, L3=1%
    'matching_percents' => array_values(array_filter(array_map('trim', explode(',', env('MLM_MATCHING_PERCENTS', '3,2,1'))), 'strlen')),

    // Pairing ON/OFF dan persen
    'enable_pairing' => (bool) env('MLM_ENABLE_PAIRING', false),
    'pairing_percent' => (float) env('MLM_PAIRING_PERCENT', 10.0),

    // Model member & field kunci
    'member_model' => env('MLM_MEMBER_MODEL', 'App\\Models\\Customer'),
    'member_pk'    => env('MLM_MEMBER_PK', 'id'),
    'member_sponsor_fk' => env('MLM_MEMBER_SPONSOR_FK', 'sponsor_id'),

    // Skala pembulatan
    'scale' => (int) env('MLM_SCALE', 2),

    // Catatan / prefix untuk idempoten
    'idempotency_note_prefix' => env('MLM_IDEMPOTENCY_PREFIX', 'ORDER-BONUS-'),
];
