<?php

use App\Mail\SubscribeEmail;

Route::get('/tes-email', function () {
    Mail::to('alangentina95@gmail.com')->send(new SubscribeEmail([
        'logo_url'=> asset('images/logo-puranura-id.png'),
        'banner_image_url'=> asset('images/galaxy-z-flip7-share-image.png'),
        'main_title' => 'New Basic Collection Launch!',
        'main_description' => 'Kami meluncurkan koleksi terbaru dengan diskon spesial 30%!',
        'shop_now_url' => 'https://puranusa.id',
        'featured_product' => [
            'name' => 'Premium Wool Sweater',
            'old_price' => '$45.00',
            'new_price' => '$30.00',
            'description' => 'Sweater wol terbaik, sangat nyaman dan cocok untuk segala musim.',
            'link' => 'https://puranusa.id',
            'image_url_small' => asset('images/smartphone.png'),
            'image_url_full_width' => asset('images/galaxy-z-flip7-share-image.png')
        ]
    ]));
    return 'Sent.';
});
