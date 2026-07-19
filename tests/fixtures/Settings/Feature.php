<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Settings;

use Marktic\Settings\Settings\Contracts\SelectOptionProviderInterface;

enum Feature: string implements SelectOptionProviderInterface
{
    case DarkMode = 'dark_mode';
    case Newsletter = 'newsletter';
    case TwoFactor = 'two_factor';

    public function label(): string
    {
        return match($this) {
            self::DarkMode => 'Dark Mode',
            self::Newsletter => 'Newsletter',
            self::TwoFactor => 'Two-Factor Auth',
        };
    }
}
