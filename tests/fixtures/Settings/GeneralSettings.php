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

    public static function group(): string
    {
        return 'general';
    }

    public static function repository(): ?string
    {
        return null;
    }
}
