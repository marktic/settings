<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Attributes;

use Marktic\Settings\Settings\Enums\SettingType;

/**
 * Declares the setting type for a property using a PHP attribute.
 *
 * This is an alternative to overriding AbstractSettings::settingTypes().
 * When both are present, settingTypes() takes precedence.
 *
 * Example:
 *
 *     #[AsSettingType('date')]
 *     public string $launch_date = '2026-01-01';
 *
 *     #[AsSettingType(SettingType::Email)]
 *     public string $contact_email = '';
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class AsSettingType
{
    public readonly string $type;

    public function __construct(string|SettingType $type)
    {
        $this->type = $type instanceof SettingType ? $type->value : strtolower($type);
    }
}
