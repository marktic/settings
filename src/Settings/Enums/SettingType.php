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
    case Date = 'date';
    case DateTime = 'datetime';
    case Email = 'email';
    case Url = 'url';

    public function cast(string $value): mixed
    {
        return match($this) {
            self::String => $value,
            self::Json => json_decode($value, true),
            self::Integer => (int) $value,
            self::Float => (float) $value,
            self::Boolean => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::Date => $this->normalizeDate($value),
            self::DateTime => $this->normalizeDateTime($value),
            self::Email => trim($value),
            self::Url => trim($value),
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
            self::Date => $this->normalizeDate((string) $value),
            self::DateTime => $this->normalizeDateTime((string) $value),
            self::Email => trim((string) $value),
            self::Url => trim((string) $value),
        };
    }

    private function normalizeDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($date instanceof \DateTimeImmutable) {
            return $date->format('Y-m-d');
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d');
        } catch (\Exception) {
            return $value;
        }
    }

    private function normalizeDateTime(string $value): string
    {
        $value = trim(str_replace('T', ' ', $value));
        if ($value === '') {
            return '';
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return $value;
        }
    }
}
