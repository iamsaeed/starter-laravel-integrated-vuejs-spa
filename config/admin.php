<?php

return [
    // Whitelisted user IDs that can access admin panel
    // Add admin user IDs here
    'id' => env('ADMIN_USER_IDS') ? explode(',', env('ADMIN_USER_IDS')) : [1, 39],
];
