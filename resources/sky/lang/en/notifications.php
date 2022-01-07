<?php

return [
    'errorGreeting' => 'Whoops!',
    'successGreeting' => 'Hello',
    'footer' => '&copy; ' . date('Y') . ' ' . config('app.name') . ' All rights reserved.',
    'salutation' => 'Regards,<br>' . config('app.name'),
    'actionText' => 'If you’re having trouble clicking the ":action" button, copy and paste the URL below into your web browser: [:url](:url)',

    'resetEmail' => [
        'subject' => 'Your Password Reset Link',
        'greeting' => 'Hello :name,',
        'intro' => 'You are receiving this email because we received a password reset request for your account.',
        'action' => 'Reset Password',
        'outro' => 'If you did not request a password reset, no further action is required.',
    ],

    'taskCreated' => [
        'subject' => 'New Task: :name',
        'greeting' => 'Hello :name,',
        'intro' => 'You are receiving this email because you have been assigned a new task by :name.',
        'action' => 'View Task',
        'outro' => 'Deadline: :deadline',
    ],

    'taskExpired' => [
        'subject' => 'Expired Task: :name',
        'greeting' => 'Hello :name,',
        'intro' => 'A task assigned to you expired today.',
        'action' => 'View Task',
    ],

    'viewingExpired' => [
        'subject' => 'Expired Viewing: :id',
        'greeting' => 'Hello :name,',
        'intro' => 'A scheduled viewing expired today.',
        'action' => 'See Viewings',
    ],

    'viewingApproaching' => [
        'subject' => 'Viewing scheduled for tomorrow: :id',
        'greeting' => 'Hello :name,',
        'intro' => 'There is a viewing scheduled for tomorrow.',
        'action' => 'See Viewings',
    ],
];
