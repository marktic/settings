<?php

declare(strict_types=1);

namespace Marktic\Settings\Utility;

use ByTIC\PackageBase\Utility\ModelFinder;
use Marktic\Settings\Settings\Models\Settings;
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



    protected static function packageName(): string
    {
        return \Marktic\Settings\MktSettingsServiceProvider::NAME;
    }
}
