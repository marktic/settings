<?php

declare(strict_types=1);

namespace Marktic\Settings\Bundle\Modules\Admin\Forms\Settings;

use Marktic\Settings\AbstractSettings;
use Marktic\Settings\Bundle\Library\Form\FormModel;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Utility\MktSettings;

class DetailsForm extends FormModel
{
    private AbstractSettings $settings;

    public function setSettings(AbstractSettings $settings): void
    {
        $this->settings = $settings;
    }

    public function getSettings(): AbstractSettings
    {
        return $this->settings;
    }

    public function getModel()
    {
        return $this->getSettings();
    }

    public function initialize()
    {
        parent::initialize();

        $this->setAttrib('id', 'mkt-settings-form');

        $this->initializeSettingsFields();

        $this->addButton('save', translator()->trans('submit'));
    }

    protected function initializeSettingsFields(): void
    {
        $reflection = new \ReflectionClass($this->settings);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $name = $property->getName();
            $label = ucwords(str_replace('_', ' ', $name));
            $type = $property->getType();
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'string';

            $currentValue = $property->isInitialized($this->settings)
                ? $property->getValue($this->settings)
                : null;

            $this->addFieldForType($name, $label, $typeName, $currentValue);
        }
    }

    protected function addFieldForType(string $name, string $label, string $typeName, mixed $currentValue): void
    {
        $resolvedType = $this->resolveFieldType($name, $typeName);

        switch ($resolvedType) {
            case 'bool':
                $this->addCheckbox($name, $label);
                if ($currentValue) {
                    $this->getElement($name)->setValue('1');
                    $this->getElement($name)->setChecked(true);
                }
                break;

            case 'array':
                $encoded = is_array($currentValue) ? json_encode($currentValue, JSON_PRETTY_PRINT) : '';
                $this->addTextarea($name, $label);
                $this->getElement($name)->setValue($encoded);
                break;

            case 'date':
                $this->addDateinput($name, $label);
                if ($currentValue !== null) {
                    $this->getElement($name)->setValue($this->normalizeDate((string) $currentValue));
                }
                break;

            case 'datetime':
                $this->addDateinput($name, $label);
//                $this->setElementInputType($name, 'datetime-local');
                if ($currentValue !== null) {
                    $this->getElement($name)->setValue($this->formatDateTimeForInput((string) $currentValue));
                }
                break;

            case 'email':
                $this->addInput($name, $label);
//                $this->setElementInputType($name, 'email');
                if ($currentValue !== null) {
                    $this->getElement($name)->setValue((string) $currentValue);
                }
                break;

            case 'url':
                $this->addInput($name, $label);
//                $this->setElementInputType($name, 'url');
                if ($currentValue !== null) {
                    $this->getElement($name)->setValue((string) $currentValue);
                }
                break;

            case 'select':
                $options = $this->resolveSelectOptions($name, $typeName);
                $this->addSelect($name, $label);
                foreach ($options as $option) {
                    $this->getElement($name)->addOption($option->value, $option->label);
                }
                if ($currentValue !== null) {
                    $selectValue = $currentValue instanceof \BackedEnum
                        ? (string) $currentValue->value
                        : (string) $currentValue;
                    $this->getElement($name)->setValue($selectValue);
                }
                break;

            case 'radio':
                $options = $this->resolveSelectOptions($name, $typeName);
                $this->addRadioGroup($name, $label);
                foreach ($options as $option) {
                    $this->getElement($name)->addOption($option->value, $option->label);
                }
                if ($currentValue !== null) {
                    $radioValue = $currentValue instanceof \BackedEnum
                        ? (string) $currentValue->value
                        : (string) $currentValue;
                    $this->getElement($name)->setValue($radioValue);
                }
                break;

            case 'multiselect':
                $options = $this->resolveSelectOptions($name, $typeName);
                $this->addSelect($name, $label);
                $this->getElement($name)->setAttrib('multiple', 'multiple');
                foreach ($options as $option) {
                    $this->getElement($name)->addOption($option->value, $option->label);
                }
                if (is_array($currentValue) && count($currentValue) > 0) {
                    $checkedValues = array_map(
                        static fn(mixed $v) => $v instanceof \BackedEnum ? (string) $v->value : (string) $v,
                        $currentValue
                    );
                    $this->getElement($name)->setValue($checkedValues);
                }
                break;

            case 'checkboxgroup':
                $options = $this->resolveSelectOptions($name, $typeName);
                $this->addCheckboxGroup($name, $label);
                foreach ($options as $option) {
                    $this->getElement($name)->addOption($option->value, $option->label);
                }
                if (is_array($currentValue) && count($currentValue) > 0) {
                    $checkedValues = array_map(
                        static fn(mixed $v) => $v instanceof \BackedEnum ? (string) $v->value : (string) $v,
                        $currentValue
                    );
                    $this->getElement($name)->setValue($checkedValues);
                }
                break;

            default:
                $this->addInput($name, $label);
                if ($currentValue !== null) {
                    $this->getElement($name)->setValue((string) $currentValue);
                }
                break;
        }
    }

    public function saveToModel()
    {
        $settings = $this->getSettings();
        $reflection = new \ReflectionClass($settings);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $name = $property->getName();
            $element = $this->getElement($name);

            if ($element === null) {
                continue;
            }

            $rawValue = $element->getValue();
            $type = $property->getType();
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'string';
            $resolvedType = $this->resolveFieldType($name, $typeName);

            $property->setValue($settings, match ($resolvedType) {
                'bool' => (bool) filter_var($rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'int' => (int) $rawValue,
                'float' => (float) $rawValue,
                'array' => is_array($rawValue) ? $rawValue : (array) json_decode((string) $rawValue, true),
                'date' => $this->normalizeDate((string) $rawValue),
                'datetime' => $this->normalizeDateTime((string) $rawValue),
                'email', 'url' => trim((string) $rawValue),
                'select', 'radio' => $this->castSelectValue($typeName, (string) $rawValue),
                'multiselect', 'checkboxgroup' => is_array($rawValue) ? $rawValue : [],
                default => (string) $rawValue,
            });
        }
    }

    public function saveModel()
    {
        MktSettings::manager()->save($this->getSettings());
    }

    private function resolveFieldType(string $name, string $defaultType): string
    {
        $explicitType = $this->settings::settingType($name);
        if ($explicitType === null) {
            if ($this->isBackedEnum($defaultType)) {
                return 'select';
            }
            if ($defaultType === 'array' && $this->settings::settingOption($name) !== null) {
                return 'multiselect';
            }
            return $defaultType;
        }

        return SettingType::tryFrom($explicitType)?->value ?? $defaultType;
    }

    /**
     * Returns the SelectOption list for a select field.
     * Checks settingOption() first, then falls back to deriving from a backed enum type.
     *
     * @return \Marktic\Settings\Settings\Dto\SelectOption[]
     */
    private function resolveSelectOptions(string $name, string $typeName): array
    {
        $declared = $this->settings::settingOption($name);
        if ($declared !== null) {
            return $declared;
        }

        if ($this->isBackedEnum($typeName)) {
            return array_map(
                static fn(\UnitEnum $case) => \Marktic\Settings\Settings\Dto\SelectOption::fromEnum($case),
                $typeName::cases()
            );
        }

        return [];
    }

    /**
     * Casts a raw string form value back to the appropriate type for a select field.
     * For backed enum properties, returns the enum instance via tryFrom().
     */
    private function castSelectValue(string $typeName, string $rawValue): mixed
    {
        if ($this->isBackedEnum($typeName)) {
            return $typeName::tryFrom($rawValue) ?? $rawValue;
        }

        return $rawValue;
    }

    private function isBackedEnum(string $className): bool
    {
        if (!enum_exists($className)) {
            return false;
        }

        return (new \ReflectionEnum($className))->isBacked();
    }

    private function setElementInputType(string $name, string $type): void
    {
        $element = $this->getElement($name);
        if ($element === null) {
            return;
        }

        if (method_exists($element, 'setAttribute')) {
            $element->setAttribute('type', $type);
            return;
        }

        if (method_exists($element, 'setAttrib')) {
            $element->setAttrib('type', $type);
        }
    }

    private function formatDateTimeForInput(string $value): string
    {
        $normalized = $this->normalizeDateTime($value);
        if ($normalized === '') {
            return '';
        }

        try {
            return (new \DateTimeImmutable($normalized))->format('Y-m-d\TH:i');
        } catch (\Exception) {
            return str_replace(' ', 'T', $value);
        }
    }

    private function normalizeDate(string $value): string
    {
        return SettingType::Date->cast($value);
    }

    private function normalizeDateTime(string $value): string
    {
        return SettingType::DateTime->cast($value);
    }
}
