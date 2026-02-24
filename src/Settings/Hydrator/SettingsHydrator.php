<?php

declare(strict_types=1);

namespace Marktic\Settings\Settings\Hydrator;

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
            $property->setValue($settings, $indexed[$name]->getCastValue());
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
            $type = $this->resolveSettingType($property->getType());
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

    private function resolveSettingType(?\ReflectionType $type): SettingType
    {
        if (!$type instanceof \ReflectionNamedType) {
            return SettingType::String;
        }

        return match ($type->getName()) {
            'bool' => SettingType::Boolean,
            'int' => SettingType::Integer,
            'float' => SettingType::Float,
            'array' => SettingType::Json,
            default => SettingType::String,
        };
    }
}
