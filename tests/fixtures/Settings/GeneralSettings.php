<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Settings;

use Marktic\Settings\AbstractSettings;

class GeneralSettings extends AbstractSettings
{
    public string $site_name = 'Marktic';

    public bool $site_active = true;

    public int $max_items = 10;

    public float $tax_rate = 0.2;

    public array $supported_locales = ['en'];

    public string $launch_date = '2026-01-01';

    public string $maintenance_at = '2026-01-01 10:00:00';

    public string $support_email = 'support@marktic.test';

    public string $homepage_url = 'https://marktic.test';

    public static function group(): string
    {
        return 'general';
    }

    public static function repository(): ?string
    {
        return null;
    }

    public static function settingTypes(): array
    {
        return [
            'launch_date' => 'date',
            'maintenance_at' => 'datetime',
            'support_email' => 'email',
            'homepage_url' => 'url',
        ];
    }
}
