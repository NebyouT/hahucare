<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'ላራቬል ጫኝ',
    'next' => 'ቀጣዩ ደረጃ',
    'back' => 'ቀዳሚ',
    'finish' => 'ጫን',
    'forms' => [
        'errorTitle' => 'የሚከተሉት ስህተቶች ተከስተዋል፡-',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'እንኳን ደህና መጣህ',
        'title'   => 'ላራቬል ጫኝ',
        'message' => 'ቀላል የመጫኛ እና የማዋቀር አዋቂ።',
        'next'    => 'መስፈርቶችን ያረጋግጡ',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'ደረጃ 1 | የአገልጋይ መስፈርቶች',
        'title' => 'የአገልጋይ መስፈርቶች',
        'next'    => 'ፈቃዶችን ያረጋግጡ',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'ደረጃ 2 | ፈቃዶች',
        'title' => 'ፈቃዶች',
        'next' => 'አካባቢን አዋቅር',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'ደረጃ 3 | የአካባቢ ቅንብሮች',
            'title' => 'የአካባቢ ቅንብሮች',
            'desc' => 'እባክዎ የ<code>.env</code> ፋይልን እንዴት ማዋቀር እንደሚፈልጉ ይምረጡ።',
            'wizard-button' => 'የቅጽ አዋቂ ማዋቀር',
            'classic-button' => 'ክላሲክ ጽሑፍ አርታዒ',
        ],
        'wizard' => [
            'templateTitle' => 'ደረጃ 3 | የአካባቢ ቅንብሮች | የሚመራ ጠንቋይ',
            'title' => 'የሚመራ <code>.env</code> አዋቂ',
            'tabs' => [
                'environment' => 'አካባቢ',
                'database' => 'የውሂብ ጎታ',
                'application' => 'መተግበሪያ',
            ],
            'form' => [
                'name_required' => 'የአካባቢ ስም ያስፈልጋል።',
                'app_name_label' => 'የመተግበሪያ ስም',
                'app_name_placeholder' => 'የመተግበሪያ ስም',
                'app_environment_label' => 'የመተግበሪያ አካባቢ',
                'app_environment_label_local' => 'አካባቢያዊ',
                'app_environment_label_developement' => 'ልማት',
                'app_environment_label_qa' => 'ቅ',
                'app_environment_label_production' => 'ማምረት',
                'app_environment_label_other' => 'ሌላ',
                'app_environment_placeholder_other' => 'አካባቢህን አስገባ...',
                'app_debug_label' => 'የመተግበሪያ ማረም',
                'app_debug_label_true' => 'እውነት ነው።',
                'app_debug_label_false' => 'ውሸት',
                'app_log_level_label' => 'የመተግበሪያ ምዝግብ ማስታወሻ ደረጃ',
                'app_log_level_label_debug' => 'ማረም',
                'app_log_level_label_info' => 'መረጃ',
                'app_log_level_label_notice' => 'ማስታወቂያ',
                'app_log_level_label_warning' => 'ማስጠንቀቂያ',
                'app_log_level_label_error' => 'ስህተት',
                'app_log_level_label_critical' => 'ወሳኝ',
                'app_log_level_label_alert' => 'ማንቂያ',
                'app_log_level_label_emergency' => 'ድንገተኛ',
                'app_url_label' => 'የመተግበሪያ ዩአርኤል',
                'app_url_placeholder' => 'የመተግበሪያ ዩአርኤል',
                'db_connection_failed' => 'ከመረጃ ቋቱ ጋር መገናኘት አልተቻለም።',
                'db_connection_label' => 'የውሂብ ጎታ ግንኙነት',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'የውሂብ ጎታ አስተናጋጅ',
                'db_host_placeholder' => 'የውሂብ ጎታ አስተናጋጅ',
                'db_port_label' => 'የውሂብ ጎታ ወደብ',
                'db_port_placeholder' => 'የውሂብ ጎታ ወደብ',
                'db_name_label' => 'የውሂብ ጎታ ስም',
                'db_name_placeholder' => 'የውሂብ ጎታ ስም',
                'db_username_label' => 'የውሂብ ጎታ የተጠቃሚ ስም',
                'db_username_placeholder' => 'የውሂብ ጎታ የተጠቃሚ ስም',
                'db_password_label' => 'የውሂብ ጎታ ይለፍ ቃል',
                'db_password_placeholder' => 'የውሂብ ጎታ ይለፍ ቃል',

                'app_tabs' => [
                    'more_info' => 'ተጨማሪ መረጃ',
                    'broadcasting_title' => 'ማሰራጨት፣ መሸጎጫ፣ ክፍለ ጊዜ፣ & amp;; ወረፋ',
                    'broadcasting_label' => 'የብሮድካስት ሾፌር',
                    'broadcasting_placeholder' => 'የብሮድካስት ሾፌር',
                    'cache_label' => 'መሸጎጫ ሾፌር',
                    'cache_placeholder' => 'መሸጎጫ ሾፌር',
                    'session_label' => 'የክፍለ ጊዜ ሹፌር',
                    'session_placeholder' => 'የክፍለ ጊዜ ሹፌር',
                    'queue_label' => 'የወረፋ ሹፌር',
                    'queue_placeholder' => 'የወረፋ ሹፌር',
                    'redis_label' => 'Redis ሾፌር',
                    'redis_host' => 'Redis አስተናጋጅ',
                    'redis_password' => 'Redis የይለፍ ቃል',
                    'redis_port' => 'Redis ወደብ',

                    'mail_label' => 'ደብዳቤ',
                    'mail_driver_label' => 'የፖስታ ሹፌር',
                    'mail_driver_placeholder' => 'የፖስታ ሹፌር',
                    'mail_host_label' => 'የፖስታ አስተናጋጅ',
                    'mail_host_placeholder' => 'የፖስታ አስተናጋጅ',
                    'mail_port_label' => 'የፖስታ ወደብ',
                    'mail_port_placeholder' => 'የፖስታ ወደብ',
                    'mail_username_label' => 'የደብዳቤ ተጠቃሚ ስም',
                    'mail_username_placeholder' => 'የደብዳቤ ተጠቃሚ ስም',
                    'mail_password_label' => 'የፖስታ ይለፍ ቃል',
                    'mail_password_placeholder' => 'የፖስታ ይለፍ ቃል',
                    'mail_encryption_label' => 'የደብዳቤ ምስጠራ',
                    'mail_encryption_placeholder' => 'የደብዳቤ ምስጠራ',

                    'pusher_label' => 'ገፊ',
                    'pusher_app_id_label' => 'የግፊት መተግበሪያ መታወቂያ',
                    'pusher_app_id_palceholder' => 'የግፊት መተግበሪያ መታወቂያ',
                    'pusher_app_key_label' => 'የግፋ መተግበሪያ ቁልፍ',
                    'pusher_app_key_palceholder' => 'የግፋ መተግበሪያ ቁልፍ',
                    'pusher_app_secret_label' => 'የግፊት መተግበሪያ ምስጢር',
                    'pusher_app_secret_palceholder' => 'የግፊት መተግበሪያ ምስጢር',
                ],
                'buttons' => [
                    'setup_database' => 'የውሂብ ጎታ ማዋቀር',
                    'setup_application' => 'የማዋቀር መተግበሪያ',
                    'install' => 'ጫን',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'ደረጃ 3 | የአካባቢ ቅንብሮች | ክላሲክ አርታዒ',
            'title' => 'ክላሲክ አካባቢ አርታዒ',
            'save' => 'አስቀምጥ .env',
            'back' => 'የቅጽ አዋቂን ተጠቀም',
            'install' => 'አስቀምጥ እና ጫን',
        ],
        'success' => 'የእርስዎ .env ፋይል ቅንጅቶች ተቀምጠዋል።',
        'errors' => 'የ.env ፋይልን ማስቀመጥ አልተቻለም፣እባክዎ እራስዎ ይፍጠሩት።',
    ],

    'install' => 'ጫን',

    /*
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'ላራቬል ጫኝ በተሳካ ሁኔታ ተጭኗል',
    ],

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'መጫኑ አልቋል',
        'templateTitle' => 'መጫኑ አልቋል',
        'finished' => 'ትግበራ በተሳካ ሁኔታ ተጭኗል።',
        'migration' => 'ፍልሰት & amp;; የዘር ኮንሶል ውፅዓት፡-',
        'console' => 'የመተግበሪያ ኮንሶል ውፅዓት፡-',
        'log' => 'የመጫኛ ምዝግብ ማስታወሻ;',
        'env' => 'የመጨረሻ .env ፋይል፡-',
        'exit' => 'ለመውጣት እዚህ ጠቅ ያድርጉ',
        'user_website'=>'የተጠቃሚ ድር ጣቢያ',
        'admin_panel' =>'የአስተዳዳሪ ፓነል'

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
        'title' => 'ላራቬል ማዘመኛ',

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'እንኳን ወደ ዝማኔው በደህና መጡ',
            'message' => 'እንኳን ወደ የዝማኔ አዋቂ እንኳን በደህና መጡ።',
        ],

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'አጠቃላይ እይታ',
            'message' => '1 ማሻሻያ አለ።|የቁጥር ማሻሻያዎች አሉ።',
            'install_updates' => 'ዝመናዎችን ጫን',
        ],

        /*
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'ጨርሷል',
            'finished' => 'መተግበሪያs database has been successfully updated.',
            'exit' => 'ለመውጣት እዚህ ጠቅ ያድርጉ',
        ],

        'log' => [
            'success_message' => 'ላራቬል ጫኝ በተሳካ ሁኔታ ዘምኗል',
        ],
    ],
];
