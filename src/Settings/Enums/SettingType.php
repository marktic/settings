<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Enums;

enum SettingType: string
{
    case String = 'string';
    case Json = 'json';
    case Integer = 'integer';
    case Float = 'float';
    case Boolean = 'boolean';

    public function cast(string $value): mixed
    {
        return match($this) {
            self::String => $value,
            self::Json => json_decode($value, true),
            self::Integer => (int) $value,
            self::Float => (float) $value,
            self::Boolean => filter_var($value, FILTER_VALIDATE_BOOLEAN),
        };
    }

    public function encode(mixed $value): string
    {
        return match($this) {
            self::String => (string) $value,
            self::Json => json_encode($value, JSON_THROW_ON_ERROR),
            self::Integer => (string) (int) $value,
            self::Float => (string) (float) $value,
            self::Boolean => $value ? '1' : '0',
        };
    }
}
