<?php

return [
    'expires_minutes' => (int) env('OTP_EXPIRES_MINUTES', 10),
    'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN_SECONDS', 60),
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'max_requests_per_hour' => (int) env('OTP_MAX_REQUESTS_PER_HOUR', 5),
    'log_codes' => (bool) env('OTP_LOG_CODES', false),
];
