<?php


function getRazorpayConfig() {
    return [
        'key_id' => env('RAZORPAY_KEY_ID', ''),
        'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
    ];
}

function getRazorpayClient() {
    $config = getRazorpayConfig();
    return new Razorpay\Api\Api($config['key_id'], $config['key_secret']);
}
