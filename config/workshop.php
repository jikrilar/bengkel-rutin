<?php

return [
    'name' => env('WORKSHOP_NAME', 'Bengkel Rutin'),
    'address' => env('WORKSHOP_ADDRESS', 'Alamat bengkel akan diperbarui'),
    'phone' => env('WORKSHOP_PHONE', 'Nomor bengkel akan diperbarui'),
    'email' => env('WORKSHOP_EMAIL'),
    'timezone' => env('WORKSHOP_TIMEZONE', 'Asia/Jakarta'),
    'slot_duration_minutes' => (int) env('WORKSHOP_SLOT_DURATION_MINUTES', 60),
    'slot_capacity' => (int) env('WORKSHOP_SLOT_CAPACITY', 3),
    'hours' => env('WORKSHOP_HOURS', 'Senin - Sabtu, 08.00 - 17.00'),
];
