<?php

use Marktic\Settings\Settings\Models\Settings;
use Marktic\Settings\Utility\MktSettingsModels;

return [
    'models' => [
        MktSettingsModels::SETTINGS => Settings::class,
    ],
    'tables' => [
        MktSettingsModels::SETTINGS => Settings::TABLE,
    ],
    'database' => [
        'connection' => 'default',
        'migrations' => true,
    ],
    'cache' => [
        'path' => sys_get_temp_dir() . '/mkt_settings_cache.json',
    ],
];
