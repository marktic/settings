<?php

declare(strict_types=1);

namespace Marktic\Settings;

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
