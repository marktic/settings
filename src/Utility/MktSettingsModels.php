<?php

declare(strict_types=1);

namespace Marktic\Settings\Utility;

use ByTIC\PackageBase\Utility\ModelFinder;
use Marktic\Settings\Settings\Mapper\SettingMapper;
use Marktic\Settings\Settings\Models\Settings;
use Marktic\Settings\Settings\Storages\DatabaseStorage;
use Nip\Records\RecordManager;

class MktSettingsModels extends ModelFinder
{
    public const SETTINGS = 'settings';

    public static function settings(): Settings|RecordManager
    {
        return static::getModels(self::SETTINGS, Settings::class);
    }

    public static function settingsClass(): string
    {
        return static::getConfigVar('models.' . self::SETTINGS, Settings::class);
    }

    public static function createDatabaseStorage(): DatabaseStorage
    {
        return new DatabaseStorage(static::settings(), new SettingMapper());
    }

    protected static function packageName(): string
    {
        return \Marktic\Settings\MktSettingsServiceProvider::NAME;
    }
}
