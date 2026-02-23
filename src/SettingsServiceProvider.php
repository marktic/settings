<?php

declare(strict_types=1);

namespace Marktic\Settings;

use ByTIC\PackageBase\BaseBootableServiceProvider;
use Marktic\Settings\Utility\PackageConfig;
use Marktic\Settings\Utility\PathsHelpers;

class SettingsServiceProvider extends BaseBootableServiceProvider
{
    public const NAME = 'mkt_settings';

    public function boot(): void
    {
        parent::boot();

        \Marktic\Settings\Utility\SettingsModels::settings();
    }

    public function migrations(): ?string
    {
        if (PackageConfig::shouldRunMigrations()) {
            return \dirname(__DIR__) . '/database/migrations/';
        }

        return null;
    }

    protected function translationsPath(): ?string
    {
        return PathsHelpers::lang('/');
    }

    public function provides(): array
    {
        return array_merge(
            [],
            parent::provides()
        );
    }
}
