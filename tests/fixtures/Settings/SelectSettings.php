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

    /**
     * Radio group: explicit type 'radio', options from enum class.
     * Declared in settingTypes() so the form layer renders radio buttons.
     */
    public string $preferred_theme = 'light';

    /**
     * Checkbox group / multi-select: array property with options → auto-detected
     * as MultiSelect by the hydrator and form layer.
     */
    public array $active_features = [];

    public static function settingTypes(): array
    {
        return [
            'preferred_theme' => 'radio',
        ];
    }

    public static function settingOptions(): array
    {
        return [
            'locale' => [
                SelectOption::from('en', 'English'),
                SelectOption::from('fr', 'French'),
                SelectOption::from('ro', 'Romanian'),
            ],
            'preferred_theme' => Theme::class,
            'active_features' => Feature::class,
        ];
    }
}
