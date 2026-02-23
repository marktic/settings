<?php

declare(strict_types=1);

namespace Marktic\Settings\ModelsRelated\HasSettings;

use Marktic\Settings\Settings\Adapters\SettingAdapterInterface;
use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Utility\SettingsModels;

trait HasSettingsRecordTrait
{
    private ?SettingAdapterInterface $settingAdapter = null;

    public function getSetting(string $name, string $group = 'default'): ?SettingDto
    {
        return $this->getSettingAdapter()->find($name, $group, $this->getSettingTenantType(), $this->getSettingTenantId());
    }

    public function getSettingValue(string $name, string $group = 'default'): mixed
    {
        $dto = $this->getSetting($name, $group);
        return $dto?->getCastValue();
    }

    public function setSetting(string $name, mixed $value, string $group = 'default', SettingType $type = SettingType::String): SettingDto
    {
        $dto = $this->getSetting($name, $group) ?? new SettingDto();
        $dto->name = $name;
        $dto->group = $group;
        $dto->type = $type;
        $dto->tenantType = $this->getSettingTenantType();
        $dto->tenantId = $this->getSettingTenantId();
        $dto->setValue($value);

        return $this->getSettingAdapter()->save($dto);
    }

    public function getSettingsByGroup(string $group): array
    {
        return $this->getSettingAdapter()->all($group, $this->getSettingTenantType(), $this->getSettingTenantId());
    }

    public function deleteSetting(string $name, string $group = 'default'): void
    {
        $dto = $this->getSetting($name, $group);
        if ($dto !== null) {
            $this->getSettingAdapter()->delete($dto);
        }
    }

    public function getSettingTenantType(): string
    {
        return static::class;
    }

    abstract public function getSettingTenantId(): string|int|null;

    public function setSettingAdapter(SettingAdapterInterface $adapter): void
    {
        $this->settingAdapter = $adapter;
    }

    protected function getSettingAdapter(): SettingAdapterInterface
    {
        if ($this->settingAdapter === null) {
            $this->settingAdapter = SettingsModels::createDatabaseAdapter();
        }

        return $this->settingAdapter;
    }
}
