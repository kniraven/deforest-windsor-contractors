<?php

$adminEmails = array_values(array_filter(array_map(
    static fn (string $email): string => strtolower(trim($email)),
    explode(',', (string) env('DIRECTORY_ADMIN_EMAILS', 'nickolas@kniraven.com'))
)));

return [
    'admin_emails' => $adminEmails,

    'submission_notification_email' => env(
        'DIRECTORY_SUBMISSION_NOTIFICATION_EMAIL',
        $adminEmails[0] ?? null
    ),
];