<?php

declare(strict_types=1);

namespace Marktic\Settings;

use Marktic\Settings\Settings\Attributes\AsSettingType;
use Marktic\Settings\Settings\Dto\SelectOption;
use Marktic\Settings\Utility\MktSettings;

abstract class AbstractSettings
{
    private ?string $tenantType = null;

    private string|int|null $tenantId = null;

    /**
     * Returns the group name used to scope this settings class in storage.
     *
     * Defaults to a snake_case version of the short class name with the
     * "Settings" suffix removed (e.g. GeneralSettings → "general").
     * Define a NAME class constant to override this value.
     */
    public static function group(): string
    {
        if (defined(static::class . '::NAME')) {
            return constant(static::class . '::NAME');
        }

        $class = static::class;
        $pos = strrpos($class, '\\');
        $shortName = $pos !== false ? substr($class, $pos + 1) : $class;
        $name = preg_replace('/Settings$/', '', $shortName) ?: $shortName;

        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($name)));
    }

    /**
     * Returns the namespace used to scope this settings class in storage.
     *
     * Returns null by default. Define a NAMESPACE class constant to set a value.
     */
    public static function settingsNamespace(): ?string
    {
        if (defined(static::class . '::NAMESPACE')) {
            return constant(static::class . '::NAMESPACE');
        }

        return null;
    }

    /**
     * Returns the repository/storage key to use for this settings class.
     * Return null to use the package default storage.
     */
    public static function repository(): ?string
    {
        return null;
    }

    /**
     * Returns explicit setting types for properties that cannot be inferred
     * from PHP scalar types (for example: date, datetime, email, url).
     *
     * @return array<string, string>
     */
    public static function settingTypes(): array
    {
        return [];
    }

    public static function settingType(string $property): ?string
    {
        $type = static::settingTypes()[$property] ?? null;

        if (is_string($type)) {
            return strtolower($type);
        }

        return static::settingTypeFromAttribute($property);
    }

    /**
     * Reads the setting type declared via the #[AsSettingType] attribute on the property,
     * or returns null when the attribute is not present.
     */
    private static function settingTypeFromAttribute(string $property): ?string
    {
        try {
            $reflection = new \ReflectionProperty(static::class, $property);
        } catch (\ReflectionException) {
            return null;
        }

        $attributes = $reflection->getAttributes(AsSettingType::class);
        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance()->type;
    }

    /**
     * Returns a map of property name → options definition for select settings.
     *
     * Each value may be either:
     * - A backed enum FQCN string (e.g. `Theme::class`): cases are resolved automatically.
     * - An array of SelectOption objects: used as-is.
     *
     * For properties typed directly as a backed enum, options are also derived
     * automatically without requiring an entry here.
     *
     * @return array<string, class-string|\BackedEnum|SelectOption[]>
     */
    public static function settingOptions(): array
    {
        return [];
    }

    /**
     * Returns the resolved SelectOption list for a given property, or null when
     * no options are declared and the property type is not a backed enum.
     *
     * @return SelectOption[]|null
     */
    public static function settingOption(string $property): ?array
    {
        $definition = static::settingOptions()[$property] ?? null;

        if ($definition === null) {
            return null;
        }

        if (is_string($definition) && enum_exists($definition)) {
            return array_map(
                static fn(\UnitEnum $case) => SelectOption::fromEnum($case),
                $definition::cases()
            );
        }

        if (is_array($definition)) {
            return $definition;
        }

        return null;
    }

    public function getTenantType(): ?string
    {
        return $this->tenantType;
    }

    public function getTenantId(): string|int|null
    {
        return $this->tenantId;
    }

    /** @internal Used by SettingsManager to inject tenant context. */
    public function setTenantContext(?string $tenantType, string|int|null $tenantId): void
    {
        $this->tenantType = $tenantType;
        $this->tenantId = $tenantId;
    }

    public function save(): static
    {
        MktSettings::manager()->save($this);
        return $this;
    }
}
