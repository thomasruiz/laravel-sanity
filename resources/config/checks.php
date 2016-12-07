<?php

return [
    'production' => [
        'app.env'     => 'production',
        'app.debug'   => false,
        'app.app_url' => '__not____regex__/localhost/',
        'mail.driver' => '__not__log'
    ],

    'dev' => [
        'app.env'     => 'local',
        'app.debug'   => true,
        'mail.driver' => 'log',
    ],
];
