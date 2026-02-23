<?php

declare(strict_types=1);

namespace Marktic\Settings\Bundle\Modules\Admin\Forms\Settings;

use Marktic\Settings\AbstractSettings;
use Marktic\Settings\Bundle\Library\Form\FormModel;

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
        switch ($typeName) {
            case 'bool':
                $this->addCheckbox($name, $label);
                if ($currentValue) {
                    $this->getElement($name)->setValue('1');
                }
                break;

            case 'array':
                $encoded = is_array($currentValue) ? json_encode($currentValue, JSON_PRETTY_PRINT) : '';
                $this->addTextarea($name, $label);
                $this->getElement($name)->setValue($encoded);
                break;

            default:
                $this->addInput($name, $label);
                if ($currentValue !== null) {
                    $this->getElement($name)->setValue((string) $currentValue);
                }
                break;
        }
    }
}
