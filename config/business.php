<?php

return [
    'legal_name' => env('BUSINESS_LEGAL_NAME', 'chain earn pvt.ltd'),
    'address' => env('BUSINESS_ADDRESS') ?: 'Bhosalewasti, Indapur, Nimgaon Ketki, Pune, Maharashtra - 413120',
    'email' => env('BUSINESS_SUPPORT_EMAIL') ?: 'info@chainearn.in',
    'phone' => env('BUSINESS_SUPPORT_PHONE') ?: '8308002855',
    'privacy_contact' => env('BUSINESS_PRIVACY_CONTACT'),
    'privacy_email' => env('BUSINESS_PRIVACY_EMAIL') ?: 'info@chainearn.in',
];
