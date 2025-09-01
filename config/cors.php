<?php


return [
'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'],
// 'allowed_origins' => ['http://localhost:5173','http://localhost:3000','https://gov-complaints-platform.onrender.com'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
];
