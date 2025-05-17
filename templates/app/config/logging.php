<?php

return [
    /**
     * Default log channel
     */
    'default' => env('LOG_CHANNEL', 'daily'),
    
    /**
     * Available log channels
     */
    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => storagePath('logs/app.log'),
            'level' => 'debug',
        ],
        
        'daily' => [
            'driver' => 'daily',
            'path' => storagePath('logs/app.log'),
            'level' => 'debug',
            'days' => 14,
        ],
        
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'LightWeight Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => 'critical',
        ],
        
        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],
        
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],
    ],
];
