<?php

return [
    'parser' => [
        'name'          => 'Spamexperts',
        'enabled'       => true,
        'report_file'   => false,
        'sender_map'    => [
            '/noreply@spamlogin.com/',
        ],
        'body_map'      => [
            '/User-Agent:\ Spampanel/',
            '/User-Agent:\ SpamExperts/',
        ],
    ],

    'feeds' => [
        'default' => [
            'class'     => 'SPAM',
            'type'      => 'Abuse',
            'enabled'   => true,
            'fields'    => [
                'Arrival-Date',
                'Authentication-Results',
                'Source-IP',
                '',
            ],
            'filters'    => [
                //
            ],
        ],

    ],
];

