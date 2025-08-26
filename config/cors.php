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

// return [

//     /*
//     |--------------------------------------------------------------------------
//     | Cross-Origin Resource Sharing (CORS) Configuration
//     |--------------------------------------------------------------------------
//     */

//     'paths' => ['api/*', 'sanctum/csrf-cookie'],
// 'allowed_methods' => ['*'],

// 'allowed_origins' => [
//     'http://localhost',
//     'http://localhost:3000',
//     'http://localhost:8080',
//     'http://localhost:5173',
//     'http://localhost:4200',
//     'http://localhost:5000',
//     'http://localhost:8000',
    
//     'http://127.0.0.1',
//     'http://127.0.0.1:3000',
//     'http://127.0.0.1:8080',
//     'http://127.0.0.1:5173',
//     'http://127.0.0.1:4200',
//     'http://127.0.0.1:5000',
//     'http://127.0.0.1:8000',

//     'https://localhost',
//     'https://localhost:3000',
//     'https://localhost:8080',
//     'https://localhost:5173',
//     'https://localhost:4200',
//     'http://127.0.0.1:5500',
//     'https://localhost:5000',
//     'https://localhost:8000',

//     'https://127.0.0.1',
//     'https://127.0.0.1:3000',
//     'https://127.0.0.1:8080',
//     'https://127.0.0.1:5173',
//     'https://127.0.0.1:4200',
//     'https://127.0.0.1:5000',
//     'https://127.0.0.1:8000',

//     'https://gov-complaints-platform.onrender.com',
//     'https://cors-test.codehappy.dev',
// ],



//     'allowed_origins_patterns' => [],

//     'allowed_headers' => [
//         'Content-Type',
//         'X-Requested-With',
//         'Authorization',
//         'Accept',
//         'Origin',
//     ],

//     'exposed_headers' => [],

//     'max_age' => 0,

//     'supports_credentials' => false,

// ];
