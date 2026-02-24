<?php

declare(strict_types=1);

namespace Marktic\Settings\Utility;

use Marktic\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\Mapper\SettingMapper;
use Marktic\Settings\MktSettingsManager;
use Marktic\Settings\Storages\DatabaseStorage;

class MktSettings
{

    public static function manager(): MktSettingsManager
    {
        static $manager;
        if ($manager === null) {
            $manager = new MktSettingsManager(
                self::databaseStorage(),
                new SettingsHydrator()
            );
        }
        return $manager;
    }

    public static function databaseStorage(): DatabaseStorage
    {
        return new DatabaseStorage(MktSettingsModels::settings(), new SettingMapper());
    }
}
