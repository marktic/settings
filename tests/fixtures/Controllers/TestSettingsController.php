<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Fixtures\Controllers;

use Marktic\Settings\AbstractSettings;
use Marktic\Settings\Bundle\Modules\Admin\Controllers\SettingsControllerTrait;
use Marktic\Settings\Bundle\Modules\Admin\Forms\Settings\DetailsForm;
use Marktic\Settings\MktSettingsManager;
use Marktic\Settings\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\Settings\Mapper\SettingMapper;
use Marktic\Settings\Settings\Storages\FileStorage;

/**
 * Concrete test double for SettingsControllerTrait.
 * Provides stub implementations of controller-layer dependencies and
 * replaces the default database storage with a temporary FileStorage.
 */
class TestSettingsController
{
    use SettingsControllerTrait;

    private string $storageFile;

    public function __construct(string $storageFile)
    {
        $this->storageFile = $storageFile;
    }

    /**
     * Expose the protected method for direct testing.
     */
    public function exposePopulateSettingsFromForm(AbstractSettings $settings, DetailsForm $form): void
    {
        $this->populateSettingsFromForm($settings, $form);
    }

    /**
     * Expose the settings manager for assertions in integration tests.
     */
    public function exposeGetSettingsManager(): MktSettingsManager
    {
        return $this->getSettingsManager();
    }

    /**
     * Override to use FileStorage instead of the database.
     */
    protected function getSettingsManager(): MktSettingsManager
    {
        if ($this->settingsManager === null) {
            $storage = new FileStorage($this->storageFile, new SettingMapper());
            $this->settingsManager = new MktSettingsManager($storage, new SettingsHydrator());
        }

        return $this->settingsManager;
    }

    // -------------------------------------------------------------------------
    // Stub controller-layer methods referenced by SettingsControllerTrait
    // -------------------------------------------------------------------------

    protected function flashMessage(string $message, string $type = 'info'): void
    {
        // intentionally empty in tests
    }

    protected function redirect(string $url): void
    {
        // intentionally empty in tests
    }

    protected function currentUrl(): string
    {
        return '/settings';
    }
}
