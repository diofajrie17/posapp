<?php

return [
    'token'       => env('WHATSAPP_TOKEN'),
    'phone_id'    => env('WHATSAPP_PHONE_ID'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v20.0'),
    'default_template' => env('WHATSAPP_DEFAULT_TEMPLATE', null),
    'sender_name' => env('WHATSAPP_SENDER_NAME', 'GYM'),
    
    // Membership expiration notifications
    'notifications_enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', false),
    'expiration_notice_days' => env('WHATSAPP_EXPIRATION_NOTICE_DAYS', '7,3,1'),
];
