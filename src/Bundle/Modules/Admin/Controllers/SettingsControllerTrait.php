<?php

declare(strict_types=1);

namespace Marktic\Settings\Bundle\Modules\Admin\Controllers;

use Marktic\Settings\AbstractSettings;
use Marktic\Settings\Bundle\Modules\Admin\Forms\Settings\DetailsForm;
use Marktic\Settings\MktSettingsManager;
use Marktic\Settings\Utility\MktSettings;
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
        $settings = $this->getSettingsManager()->get($settingsClass, $this->getSettingsTenant());

        $form = $this->buildSettingsForm($settings);

        if ($form->submited()) {
            $this->populateSettingsFromForm($settings, $form);
            $this->getSettingsManager()->save($settings);

            $this->flashRedirect(
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

    protected function getSettingsManager(): MktSettingsManager
    {
        if ($this->settingsManager === null) {
            $this->settingsManager = MktSettings::manager();
        }
        return $this->settingsManager;
    }
}
