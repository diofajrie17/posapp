<?php

return [
    'token'       => env('WHATSAPP_TOKEN'),
    'phone_id'    => env('WHATSAPP_PHONE_ID'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v20.0'),
    'default_template' => env('WHATSAPP_DEFAULT_TEMPLATE', null),
    'sender_name' => env('WHATSAPP_SENDER_NAME', 'GYM'),
];
