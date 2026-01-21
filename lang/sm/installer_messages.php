<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'Laravel Installer',
    'next' => 'Talaabada Xigta',
    'back' => 'Hore',
    'finish' => 'Ku rakib',
    'forms' => [
        'errorTitle' => 'Khaladaadka soo socda ayaa dhacay:',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Soo dhawoow',
        'title'   => 'Laravel Installer',
        'message' => 'Rakibaadda Fudud iyo Dejinta Wizard.',
        'next'    => 'Hubi Shuruudaha',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Talaabada 1aad | Shuruudaha Server-ka',
        'title' => 'Shuruudaha Server-ka',
        'next'    => 'Hubi ogolaanshaha',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Tallaabada 2 | Ogolaanshaha',
        'title' => 'Ogolaanshaha',
        'next' => 'Habee Deegaanka',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Tallaabada 3 | Dejinta Deegaanka',
            'title' => 'Dejinta Deegaanka',
            'desc' => 'Fadlan dooro sida aad rabto inaad u habayso abka <code>.env</code> faylka.',
            'wizard-button' => 'Dejinta Wizard Form',
            'classic-button' => 'Tafatiraha qoraalka caadiga ah',
        ],
        'wizard' => [
            'templateTitle' => 'Tallaabada 3 | Dejinta Deegaanka | Wizard hanuunsan',
            'title' => 'La hagayo <code>.env</code> Saaxir',
            'tabs' => [
                'environment' => 'Deegaanka',
                'database' => 'Database',
                'application' => 'Codsiga',
            ],
            'form' => [
                'name_required' => 'Magaca deegaanka ayaa loo baahan yahay.',
                'app_name_label' => 'Magaca App',
                'app_name_placeholder' => 'Magaca App',
                'app_environment_label' => 'Deegaanka App',
                'app_environment_label_local' => 'Maxaliga ah',
                'app_environment_label_developement' => 'Horumarin',
                'app_environment_label_qa' => 'Qa',
                'app_environment_label_production' => 'Wax soo saar',
                'app_environment_label_other' => 'Mid kale',
                'app_environment_placeholder_other' => 'Geli deegaankaaga...',
                'app_debug_label' => 'Debug abka',
                'app_debug_label_true' => 'Run',
                'app_debug_label_false' => 'Been',
                'app_log_level_label' => 'Heerka Log App-ka',
                'app_log_level_label_debug' => 'qaladka',
                'app_log_level_label_info' => 'macluumaadka',
                'app_log_level_label_notice' => 'ogeysiis',
                'app_log_level_label_warning' => 'digniin',
                'app_log_level_label_error' => 'qalad',
                'app_log_level_label_critical' => 'dhaliil',
                'app_log_level_label_alert' => 'feejigan',
                'app_log_level_label_emergency' => 'degdeg ah',
                'app_url_label' => 'App Url',
                'app_url_placeholder' => 'App Url',
                'db_connection_failed' => 'Waa lagu xidhi kari waayay kaydka xogta',
                'db_connection_label' => 'Isku xirka Database',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Martigeliyaha Database',
                'db_host_placeholder' => 'Martigeliyaha Database',
                'db_port_label' => 'Deked Database',
                'db_port_placeholder' => 'Deked Database',
                'db_name_label' => 'Magaca Database',
                'db_name_placeholder' => 'Magaca Database',
                'db_username_label' => 'Magaca Isticmaalaha Xogta',
                'db_username_placeholder' => 'Magaca Isticmaalaha Xogta',
                'db_password_label' => 'Keydka Xogta Keydka',
                'db_password_placeholder' => 'Keydka Xogta Keydka',

                'app_tabs' => [
                    'more_info' => 'Macluumaad dheeraad ah',
                    'broadcasting_title' => 'Baahinta, Caching, Fadhiga, & amp; Safka',
                    'broadcasting_label' => 'Darawalka Warbaahineed',
                    'broadcasting_placeholder' => 'Darawalka Warbaahineed',
                    'cache_label' => 'Dareewalka Cache',
                    'cache_placeholder' => 'Dareewalka Cache',
                    'session_label' => 'Darawalka Kulanka',
                    'session_placeholder' => 'Darawalka Kulanka',
                    'queue_label' => 'Darawalka safka',
                    'queue_placeholder' => 'Darawalka safka',
                    'redis_label' => 'Redis Driver',
                    'redis_host' => 'Martigeliyaha Redis',
                    'redis_password' => 'Redis Password',
                    'redis_port' => 'Dekedda Redis',

                    'mail_label' => 'Boostada',
                    'mail_driver_label' => 'Dareewalka Boostada',
                    'mail_driver_placeholder' => 'Dareewalka Boostada',
                    'mail_host_label' => 'Martigeliyaha Boostada',
                    'mail_host_placeholder' => 'Martigeliyaha Boostada',
                    'mail_port_label' => 'Dekedda Boostada',
                    'mail_port_placeholder' => 'Dekedda Boostada',
                    'mail_username_label' => 'Magaca isticmaalaha boostada',
                    'mail_username_placeholder' => 'Magaca isticmaalaha boostada',
                    'mail_password_label' => 'Furaha Furaha',
                    'mail_password_placeholder' => 'Furaha Furaha',
                    'mail_encryption_label' => 'Sireeynta boostada',
                    'mail_encryption_placeholder' => 'Sireeynta boostada',

                    'pusher_label' => 'Riix',
                    'pusher_app_id_label' => 'Id Appka riixaya',
                    'pusher_app_id_palceholder' => 'Id Appka riixaya',
                    'pusher_app_key_label' => 'Furaha App-ka riixaya',
                    'pusher_app_key_palceholder' => 'Furaha App-ka riixaya',
                    'pusher_app_secret_label' => 'Riix App Secret',
                    'pusher_app_secret_palceholder' => 'Riix App Secret',
                ],
                'buttons' => [
                    'setup_database' => 'Dejinta Database',
                    'setup_application' => 'Dejinta Codsiga',
                    'install' => 'Ku rakib',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Tallaabada 3 | Dejinta Deegaanka | Tifatiraha Classic',
            'title' => 'Tifaftiraha Deegaanka Classic',
            'save' => 'Keydso .env',
            'back' => 'Isticmaal Form Wizard',
            'install' => 'Keydi oo ku rakib',
        ],
        'success' => 'Dejinta faylkaaga .env waa la kaydiyay.',
        'errors' => 'Ma awoodo in la kaydiyo faylka .env, Fadlan gacanta ku samee.',
    ],

    'install' => 'Ku rakib',

    /*
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Rakibadihii Laravel si guul leh ayaa loo rakibay',
    ],

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Rakibadihii wuu dhamaaday',
        'templateTitle' => 'Rakibadihii wuu dhamaaday',
        'finished' => 'Codsiga si guul leh ayaa loo rakibay',
        'migration' => 'Tahriibka &amp; Soo saarida Console abuur:',
        'console' => 'Soo saarida Console Application:',
        'log' => 'Gelida Rakibaadda:',
        'env' => 'Final .env:',
        'exit' => 'Riix halkan si aad uga baxdo',
        'user_website'=>'Mareegta Isticmaalaha',
        'admin_panel' =>'Guddiga maamulka'

    ],

    /*
     *
     * Update specific translations
     *
     */
    'updater' => [
        /*
         *
         * Shared translations.
         *
         */
        'title' => 'Laravel Updater',

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Ku Soo Dhawoow Cusboonaysiinta',
            'message' => 'Ku soo dhawoow saaxir cusboonaysiinta.',
        ],

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Dulmar',
            'message' => 'Waxaa jira 1 update.|Waxaa jira :cusbooneysii lambarka.',
            'install_updates' => 'Ku rakib Cusbooneysii',
        ],

        /*
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Dhammaatay',
            'finished' => 'Codsiga s database has been successfully updated.',
            'exit' => 'Riix halkan si aad uga baxdo',
        ],

        'log' => [
            'success_message' => 'Rakibadihii Laravel si guul leh ayaa loo cusboonaysiiyay',
        ],
    ],
];
