<?php

declare(strict_types=1);

namespace Marktic\Settings\Utility;

use Marktic\Settings\SettingsServiceProvider;
use Nip\Utility\Traits\SingletonTrait;

class PackageConfig extends \ByTIC\PackageBase\Utility\PackageConfig
{
    use SingletonTrait;

    protected $name = SettingsServiceProvider::NAME;

    public static function configPath(): string
    {
        return __DIR__ . '/../../config/mkt_settings.php';
    }

    public static function tableName(string $name, $default = null): mixed
    {
        return static::instance()->get('tables.' . $name, $default);
    }

    public static function databaseConnection(): ?string
    {
        return (string) static::instance()->get('database.connection');
    }

    public static function shouldRunMigrations(): bool
    {
        return false !== static::instance()->get('database.migrations', false);
    }
}
