<?php

declare(strict_types=1);

namespace Marktic\Settings;

use Marktic\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\Storages\SettingStorageInterface;

class MktSettingsManager
{
    /** @var array<string, AbstractSettings> */
    private array $instances = [];

    /** @var array<string, SettingStorageInterface> */
    private array $namedStorages = [];

    public function __construct(
        private readonly SettingStorageInterface $defaultStorage,
        private readonly SettingsHydrator $hydrator
    ) {
    }

    /**
     * Returns a hydrated settings instance for the given class.
     * Instances are cached per class + tenant combination, so repeated calls
     * with the same arguments return the exact same object.
     *
     * @template T of AbstractSettings
     * @param class-string<T> $settingsClass
     * @param object|null $tenant Optional tenant record (must implement SettingsTenantInterface or expose getSettingTenantType/Id)
     * @return T
     */
    public function get(string $settingsClass, ?object $tenant = null): AbstractSettings
    {
        [$tenantType, $tenantId] = $this->resolveTenant($tenant);
        $key = $this->buildInstanceKey($settingsClass, $tenantType, $tenantId);

        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        /** @var AbstractSettings $settings */
        $settings = new $settingsClass();
        $settings->setTenantContext($tenantType, $tenantId);

        $storage = $this->resolveStorage($settingsClass);
        $dtos = $storage->all($settingsClass::group(), $tenantType, $tenantId, $settingsClass::settingsNamespace());
        $this->hydrator->hydrate($settings, $dtos);

        $this->instances[$key] = $settings;

        return $settings;
    }

    /**
     * Persists all properties of a settings instance back to its storage.
     */
    public function save(AbstractSettings $settings): void
    {
        $storage = $this->resolveStorage($settings::class);
        $group = $settings::group();

        $existingDtos = $storage->all($group, $settings->getTenantType(), $settings->getTenantId(), $settings::settingsNamespace());
        $dtos = $this->hydrator->extract($settings, $existingDtos);

        foreach ($dtos as $dto) {
            $storage->save($dto);
        }
    }

    /**
     * Registers a named storage that settings classes can reference via repository().
     */
    public function addStorage(string $name, SettingStorageInterface $storage): void
    {
        $this->namedStorages[$name] = $storage;
    }

    /**
     * Clears the internal instance cache (useful in tests or long-running processes).
     */
    public function flush(): void
    {
        $this->instances = [];
    }

    private function resolveStorage(string $settingsClass): SettingStorageInterface
    {
        $repositoryName = $settingsClass::repository();
        if ($repositoryName !== null && isset($this->namedStorages[$repositoryName])) {
            return $this->namedStorages[$repositoryName];
        }

        return $this->defaultStorage;
    }

    private function resolveTenant(?object $tenant): array
    {
        if ($tenant === null) {
            return [null, null];
        }

        if ($tenant instanceof SettingsTenantInterface) {
            return [$tenant->getSettingTenantType(), $tenant->getSettingTenantId()];
        }

        if (method_exists($tenant, 'getSettingTenantType') && method_exists($tenant, 'getSettingTenantId')) {
            return [$tenant->getSettingTenantType(), $tenant->getSettingTenantId()];
        }

        $tenantName = method_exists($tenant, 'getManager') ? $tenant->getManager()->getMorphName() : get_class($tenant);

        return [$tenantName, property_exists($tenant, 'id') ? $tenant->id : null];
    }

    private function buildInstanceKey(
        string $settingsClass,
        ?string $tenantType,
        string|int|null $tenantId
    ): string {
        return $settingsClass . '|' . ($tenantType ?? '') . ':' . ($tenantId ?? '');
    }
}
