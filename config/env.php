<?php
return [
    'db' => [
        'dsn' => 'mysql:host=128.0.0.1;dbname=apiphp;charset=utf8mb4',
        'user' => 'root',
        'pass' => ''
    ],
    'app' => [
        'env' => 'local',
        'base_url' => 'http://localhost/api_php_native_sarahh/public',
        'jwt_secret' => 'SarahAlexandra_MoonlightStar22>=32_chars',
        'allowed_origins' => ['http://localhost:3000', 'http://localhost']
    ]
];
