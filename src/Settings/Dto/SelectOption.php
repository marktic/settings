<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Dto;

use Marktic\Settings\Settings\Contracts\SelectOptionProviderInterface;

/**
 * Represents a single option in a select setting.
 *
 * `value` is the raw string stored in the database.
 * `label` is the human-readable string shown in a UI select element.
 */
final class SelectOption
{
    private function __construct(
        public readonly string $value,
        public readonly string $label,
    ) {}

    public static function from(string $value, string $label): self
    {
        return new self($value, $label);
    }

    /**
     * Builds a SelectOption from a backed or pure enum case.
     *
     * - The stored value is the backed enum's string/int value (cast to string),
     *   or the case name for pure (non-backed) enums.
     * - The label comes from SelectOptionProviderInterface::label() if implemented,
     *   or falls back to the case name.
     */
    public static function fromEnum(\UnitEnum $case): self
    {
        $value = $case instanceof \BackedEnum ? (string) $case->value : $case->name;
        $label = $case instanceof SelectOptionProviderInterface ? $case->label() : $case->name;

        return new self($value, $label);
    }
}
