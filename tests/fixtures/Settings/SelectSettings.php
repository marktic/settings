<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Settings;

use Marktic\Settings\AbstractSettings;
use Marktic\Settings\Settings\Dto\SelectOption;

class SelectSettings extends AbstractSettings
{
    /** Property typed directly as a backed enum — auto-detected as a select. */
    public Theme $theme = Theme::Light;

    /** Plain string select declared via settingOptions(). */
    public string $locale = 'en';

    public static function settingOptions(): array
    {
        return [
            'locale' => [
                SelectOption::from('en', 'English'),
                SelectOption::from('fr', 'French'),
                SelectOption::from('ro', 'Romanian'),
            ],
        ];
    }
}
