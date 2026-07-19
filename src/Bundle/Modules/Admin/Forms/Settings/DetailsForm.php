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
            $phpTypeName = $property->getType() instanceof \ReflectionNamedType
                ? $property->getType()->getName()
                : 'string';

            $currentValue = $property->isInitialized($this->settings)
                ? $property->getValue($this->settings)
                : null;

            $this->addFieldForType($name, $label, $phpTypeName, $currentValue);
        }
    }

    protected function addFieldForType(string $name, string $label, string $phpTypeName, mixed $currentValue): void
    {
        $type = $this->resolveFieldType($name, $phpTypeName);

        switch ($type) {
            case SettingType::Boolean:
                $this->addCheckbox($name, $label);
                if ($currentValue) {
                    $this->getElement($name)->setValue('1');
                    $this->getElement($name)->setChecked(true);
                }
                break;

            case SettingType::Json:
                $encoded = is_array($currentValue) ? json_encode($currentValue, JSON_PRETTY_PRINT) : '';
                $this->addTextarea($name, $label);
                $this->getElement($name)->setValue($encoded);
                break;

            case SettingType::Date:
                $this->addDateinput($name, $label);
                if ($currentValue !== null) {
                    $this->getElement($name)->setValue($this->normalizeDate((string) $currentValue));
                }
                break;

            case SettingType::DateTime:
                $this->addDateinput($name, $label);
                if ($currentValue !== null) {
                    $this->getElement($name)->setValue($this->formatDateTimeForInput((string) $currentValue));
                }
                break;

            case SettingType::Email:
            case SettingType::Url:
                $this->addInput($name, $label);
                if ($currentValue !== null) {
                    $this->getElement($name)->setValue((string) $currentValue);
                }
                break;

            case SettingType::Select:
                $options = $this->resolveSelectOptions($name, $phpTypeName);
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

            case SettingType::Radio:
                $options = $this->resolveSelectOptions($name, $phpTypeName);
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

            case SettingType::MultiSelect:
                $options = $this->resolveSelectOptions($name, $phpTypeName);
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

            case SettingType::CheckboxGroup:
                $options = $this->resolveSelectOptions($name, $phpTypeName);
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
            $phpTypeName = $property->getType() instanceof \ReflectionNamedType
                ? $property->getType()->getName()
                : 'string';
            $type = $this->resolveFieldType($name, $phpTypeName);

            $property->setValue($settings, match ($type) {
                SettingType::Boolean => (bool) filter_var($rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                SettingType::Integer => (int) $rawValue,
                SettingType::Float => (float) $rawValue,
                SettingType::Json => is_array($rawValue) ? $rawValue : (array) json_decode((string) $rawValue, true),
                SettingType::Date => $this->normalizeDate((string) $rawValue),
                SettingType::DateTime => $this->normalizeDateTime((string) $rawValue),
                SettingType::Email, SettingType::Url => trim((string) $rawValue),
                SettingType::Select, SettingType::Radio => $this->castSelectValue($phpTypeName, (string) $rawValue),
                SettingType::MultiSelect, SettingType::CheckboxGroup => is_array($rawValue) ? $rawValue : [],
                default => (string) $rawValue,
            });
        }
    }

    public function saveModel()
    {
        MktSettings::manager()->save($this->getSettings());
    }

    /**
     * Resolves the SettingType for a property.
     *
     * Priority: explicit type from settingType() → backed-enum auto-detect
     * → PHP-type inference via SettingType::fromPhpType().
     */
    private function resolveFieldType(string $name, string $phpTypeName): SettingType
    {
        $explicit = $this->settings::settingType($name);
        if ($explicit !== null) {
            return $explicit;
        }

        if ($this->isBackedEnum($phpTypeName)) {
            return SettingType::Select;
        }

        $hasOptions = $this->settings::settingOption($name) !== null;
        return SettingType::fromPhpType($phpTypeName, $hasOptions);
    }

    /**
     * Returns the SelectOption list for a select/radio/multi-select field.
     * Checks settingOption() first, then falls back to deriving from a backed enum type.
     *
     * @return \Marktic\Settings\Settings\Dto\SelectOption[]
     */
    private function resolveSelectOptions(string $name, string $phpTypeName): array
    {
        $declared = $this->settings::settingOption($name);
        if ($declared !== null) {
            return $declared;
        }

        if ($this->isBackedEnum($phpTypeName)) {
            return array_map(
                static fn(\UnitEnum $case) => \Marktic\Settings\Settings\Dto\SelectOption::fromEnum($case),
                $phpTypeName::cases()
            );
        }

        return [];
    }

    /**
     * Casts a raw string form value back to the appropriate type for a select field.
     * For backed enum properties, returns the enum instance via tryFrom().
     */
    private function castSelectValue(string $phpTypeName, string $rawValue): mixed
    {
        if ($this->isBackedEnum($phpTypeName)) {
            return $phpTypeName::tryFrom($rawValue) ?? $rawValue;
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
