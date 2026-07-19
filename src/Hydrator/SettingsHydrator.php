<?php

declare(strict_types=1);

namespace Marktic\Settings\Hydrator;

use Marktic\Settings\AbstractSettings;
use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;

class SettingsHydrator
{
    /**
     * Populates the public properties of an AbstractSettings instance from a list of SettingDto objects.
     *
     * @param SettingDto[] $dtos
     */
    public function hydrate(AbstractSettings $settings, array $dtos): void
    {
        $indexed = [];
        foreach ($dtos as $dto) {
            $indexed[$dto->name] = $dto;
        }

        $reflection = new \ReflectionClass($settings);
        foreach ($this->getSettingProperties($reflection) as $property) {
            $name = $property->getName();
            if (!isset($indexed[$name])) {
                continue;
            }
            $castValue = $indexed[$name]->getCastValue();
            $castValue = $this->castToPropertyType($property, $castValue);
            $property->setValue($settings, $castValue);
        }
    }

    /**
     * Extracts the public properties of an AbstractSettings instance into SettingDto objects.
     * Existing DTOs (indexed by name) are updated in-place to preserve their IDs.
     *
     * @param SettingDto[] $existingDtos
     * @return SettingDto[]
     */
    public function extract(AbstractSettings $settings, array $existingDtos = []): array
    {
        $existing = [];
        foreach ($existingDtos as $dto) {
            $existing[$dto->name] = $dto;
        }

        $dtos = [];
        $reflection = new \ReflectionClass($settings);
        foreach ($this->getSettingProperties($reflection) as $property) {
            if (!$property->isInitialized($settings)) {
                continue;
            }

            $name = $property->getName();
            $type = $this->resolveSettingType($settings, $property);
            $value = $property->getValue($settings);

            if (isset($existing[$name])) {
                $dto = $existing[$name];
                $dto->type = $type;
                $dto->setValue($value);
            } else {
                $dto = new SettingDto();
                $dto->name = $name;
                $dto->group = $settings::group();
                $dto->namespace = $settings::settingsNamespace();
                $dto->type = $type;
                $dto->tenantType = $settings->getTenantType();
                $dto->tenantId = $settings->getTenantId();
                $dto->setValue($value);
            }

            $dtos[] = $dto;
        }

        return $dtos;
    }

    /**
     * @return \ReflectionProperty[]
     */
    private function getSettingProperties(\ReflectionClass $reflection): array
    {
        return array_filter(
            $reflection->getProperties(\ReflectionProperty::IS_PUBLIC),
            static fn(\ReflectionProperty $p) => !$p->isStatic()
        );
    }

    private function resolveSettingType(AbstractSettings $settings, \ReflectionProperty $property): SettingType
    {
        $explicit = $settings::settingType($property->getName());
        if ($explicit !== null) {
            return $explicit;
        }

        $phpType = $property->getType();
        if (!$phpType instanceof \ReflectionNamedType) {
            return SettingType::String;
        }

        $typeName = $phpType->getName();

        if ($this->isBackedEnum($typeName)) {
            return SettingType::Select;
        }

        $hasOptions = $settings::settingOption($property->getName()) !== null;
        return SettingType::fromPhpType($typeName, $hasOptions);
    }

    /**
     * If the property's PHP type is a backed enum, casts a raw string value back
     * to the appropriate enum instance using tryFrom().
     */
    private function castToPropertyType(\ReflectionProperty $property, mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $type = $property->getType();
        if (!$type instanceof \ReflectionNamedType) {
            return $value;
        }

        $typeName = $type->getName();
        if ($this->isBackedEnum($typeName)) {
            return $typeName::tryFrom($value) ?? $value;
        }

        return $value;
    }

    private function isBackedEnum(string $className): bool
    {
        if (!enum_exists($className)) {
            return false;
        }

        return (new \ReflectionEnum($className))->isBacked();
    }
}
