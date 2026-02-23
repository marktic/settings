<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Settings;

use Marktic\Settings\AbstractSettings;

class TenantSettings extends AbstractSettings
{
    public string $theme = 'light';

    public bool $notifications = false;

    public static function group(): string
    {
        return 'tenant';
    }
}
