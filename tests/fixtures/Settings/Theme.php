<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Settings;

use Marktic\Settings\Settings\Contracts\SelectOptionProviderInterface;

enum Theme: string implements SelectOptionProviderInterface
{
    case Light = 'light';
    case Dark = 'dark';
    case System = 'system';

    public function label(): string
    {
        return match($this) {
            self::Light => 'Light Theme',
            self::Dark => 'Dark Theme',
            self::System => 'System Default',
        };
    }
}
