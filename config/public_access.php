<?php

declare(strict_types=1);

return [
    'complaints' => [
        'per_minute' => max(1, (int) env('PUBLIC_COMPLAINTS_PER_MINUTE', 3)),
        'per_day' => max(1, (int) env('PUBLIC_COMPLAINTS_PER_DAY', 5))
    ],
    'status' => [
        'per_minute' => max(1, (int) env('PUBLIC_STATUS_PER_MINUTE', 20))
    ],
    'attendance' => [
        'per_minute' => max(1, (int) env('PUBLIC_ATTENDANCE_PER_MINUTE', 20)),
        'per_hour' => max(1, (int) env('PUBLIC_ATTENDANCE_PER_HOUR', 120))
    ],
    'biometric_events' => [
        'per_minute' => max(1, (int) env('PUBLIC_BIOMETRIC_EVENTS_PER_MINUTE', 180))
    ]
];
