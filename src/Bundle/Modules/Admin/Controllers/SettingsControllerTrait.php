<?php

declare(strict_types=1);

namespace Marktic\Settings\Bundle\Modules\Admin\Controllers;

use Marktic\Settings\AbstractSettings;
use Marktic\Settings\Bundle\Modules\Admin\Forms\Settings\DetailsForm;
use Marktic\Settings\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\MktSettingsManager;
use Marktic\Settings\Utility\MktSettingsModels;
use Nip\Controllers\Response\ResponsePayload;

/**
 * Settings admin controller trait.
 *
 * Concrete controllers should add one public method per settings group and
 * delegate each to handleSettingsGroup():
 *
 *   public function general(): void
 *   {
 *       $this->handleSettingsGroup(GeneralSettings::class);
 *   }
 *
 * @method ResponsePayload payload()
 */
trait SettingsControllerTrait
{
    use AbstractSettingsControllerTrait;

    private ?MktSettingsManager $settingsManager = null;

    /**
     * Default index action — renders a list of available settings groups.
     * Override in the concrete controller to add group links as needed.
     */
    public function index(): void
    {
        $this->payload()->with([
            'pageTitle' => translator()->trans('mkt_settings-settings.labels.title'),
        ]);
    }

    /**
     * Base method called by every public settings-group action.
     * Loads the settings class, builds the form, handles submission, and
     * passes data to the view.
     *
     * @param class-string<AbstractSettings> $settingsClass
     */
    protected function handleSettingsGroup(string $settingsClass): void
    {
        $settings = $this->getSettingsManager()->get($settingsClass);

        $form = $this->buildSettingsForm($settings);

        if ($form->submited()) {
            $this->populateSettingsFromForm($settings, $form);
            $this->getSettingsManager()->save($settings);

            $this->flashMessage(
                translator()->trans('mkt_settings-settings.messages.saved'),
                'success'
            );

            $this->redirect($this->currentUrl());
            return;
        }

        $this->payload()->with([
            'settings' => $settings,
            'settingsClass' => $settingsClass,
            'settingsGroup' => $settingsClass::group(),
            'form' => $form,
            'pageTitle' => translator()->trans('mkt_settings-settings.labels.title'),
        ]);
    }

    /**
     * Builds and initialises a settings form for the given settings instance.
     */
    protected function buildSettingsForm(AbstractSettings $settings): DetailsForm
    {
        $formClass = $this->generateSettingsFormClass();
        $form = new $formClass();
        $form->setSettings($settings);

        return $form;
    }

    protected function generateSettingsFormClass()
    {
        return DetailsForm::class;
    }

    /**
     * Populates settings properties from submitted form values.
     */
    protected function populateSettingsFromForm(AbstractSettings $settings, DetailsForm $form): void
    {
        $reflection = new \ReflectionClass($settings);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $name = $property->getName();
            $element = $form->getElement($name);

            if ($element === null) {
                continue;
            }

            $rawValue = $element->getValue();
            $type = $property->getType();
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'string';

            $property->setValue($settings, match ($typeName) {
                'bool' => (bool) filter_var($rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'int' => (int) $rawValue,
                'float' => (float) $rawValue,
                'array' => is_array($rawValue) ? $rawValue : (array) json_decode((string) $rawValue, true),
                default => (string) $rawValue,
            });
        }
    }

    protected function getSettingsManager(): MktSettingsManager
    {
        if ($this->settingsManager === null) {
            $this->settingsManager = new MktSettingsManager(
                MktSettingsModels::createDatabaseStorage(),
                new SettingsHydrator()
            );
        }

        return $this->settingsManager;
    }
}
