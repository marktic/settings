<?php

declare(strict_types=1);

namespace Marktic\Settings;

abstract class AbstractSettings
{
    private ?string $tenantType = null;

    private string|int|null $tenantId = null;

    /**
     * Returns the group name used to scope this settings class in storage.
     */
    abstract public static function group(): string;

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
}
