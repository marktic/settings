<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Bundle\Modules\Admin\Controllers;

use Marktic\Settings\MktSettingsManager;
use Marktic\Settings\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\Settings\Mapper\SettingMapper;
use Marktic\Settings\Settings\Storages\FileStorage;
use Marktic\Settings\Tests\AbstractTest;
use Marktic\Settings\Tests\Fixtures\Controllers\TestSettingsController;
use Marktic\Settings\Tests\Fixtures\Forms\TestDetailsForm;
use Marktic\Settings\Tests\Fixtures\Settings\GeneralSettings;

class SettingsControllerTraitTest extends AbstractTest
{
    private string $storageFile;
    private FileStorage $storage;
    private TestSettingsController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storageFile = sys_get_temp_dir() . '/mkt_controller_test_' . uniqid() . '.json';
        $this->storage = new FileStorage($this->storageFile, new SettingMapper());
        $this->controller = new TestSettingsController($this->storageFile);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_file($this->storageFile)) {
            unlink($this->storageFile);
        }
    }

    // -------------------------------------------------------------------------
    // populateSettingsFromForm – type casting
    // -------------------------------------------------------------------------

    public function testPopulateStringValueFromForm(): void
    {
        $settings = new GeneralSettings();
        $form = $this->buildFormWithValues($settings, ['site_name' => 'New Site Name']);

        $this->controller->exposePopulateSettingsFromForm($settings, $form);

        self::assertSame('New Site Name', $settings->site_name);
    }

    public function testPopulateBoolTrueValueFromForm(): void
    {
        $settings = new GeneralSettings();
        $settings->site_active = false;
        $form = $this->buildFormWithValues($settings, ['site_active' => '1']);

        $this->controller->exposePopulateSettingsFromForm($settings, $form);

        self::assertTrue($settings->site_active);
    }

    public function testPopulateBoolFalseValueFromForm(): void
    {
        $settings = new GeneralSettings();
        $settings->site_active = true;
        $form = $this->buildFormWithValues($settings, ['site_active' => '0']);

        $this->controller->exposePopulateSettingsFromForm($settings, $form);

        self::assertFalse($settings->site_active);
    }

    public function testPopulateIntValueFromForm(): void
    {
        $settings = new GeneralSettings();
        $form = $this->buildFormWithValues($settings, ['max_items' => '42']);

        $this->controller->exposePopulateSettingsFromForm($settings, $form);

        self::assertSame(42, $settings->max_items);
    }

    public function testPopulateFloatValueFromForm(): void
    {
        $settings = new GeneralSettings();
        $form = $this->buildFormWithValues($settings, ['tax_rate' => '0.25']);

        $this->controller->exposePopulateSettingsFromForm($settings, $form);

        self::assertSame(0.25, $settings->tax_rate);
    }

    public function testPopulateArrayValueFromFormAsJson(): void
    {
        $settings = new GeneralSettings();
        $form = $this->buildFormWithValues($settings, ['supported_locales' => '["en","de","fr"]']);

        $this->controller->exposePopulateSettingsFromForm($settings, $form);

        self::assertSame(['en', 'de', 'fr'], $settings->supported_locales);
    }

    public function testPopulateRetainsUnchangedValues(): void
    {
        $settings = new GeneralSettings();
        // Build form without overrides – form elements will have the default values
        $form = $this->buildFormWithValues($settings, []);

        $this->controller->exposePopulateSettingsFromForm($settings, $form);

        // Properties should retain their defaults because no override was applied
        self::assertSame('Marktic', $settings->site_name);
        self::assertSame(10, $settings->max_items);
    }

    // -------------------------------------------------------------------------
    // Full save flow: populate from form → save via manager → reload and verify
    // -------------------------------------------------------------------------

    public function testSettingsAreSavedAfterPopulateFromForm(): void
    {
        $manager = $this->controller->exposeGetSettingsManager();
        $settings = $manager->get(GeneralSettings::class);

        $form = $this->buildFormWithValues($settings, [
            'site_name' => 'Saved Name',
            'max_items' => '99',
        ]);

        $this->controller->exposePopulateSettingsFromForm($settings, $form);
        $manager->save($settings);

        // Reload from storage to confirm persistence
        $freshManager = new MktSettingsManager($this->storage, new SettingsHydrator());
        $reloaded = $freshManager->get(GeneralSettings::class);

        self::assertSame('Saved Name', $reloaded->site_name);
        self::assertSame(99, $reloaded->max_items);
    }

    public function testAllTypesArePersistedCorrectly(): void
    {
        $manager = $this->controller->exposeGetSettingsManager();
        $settings = $manager->get(GeneralSettings::class);

        $form = $this->buildFormWithValues($settings, [
            'site_name'        => 'Typed Site',
            'site_active'      => '0',
            'max_items'        => '5',
            'tax_rate'         => '0.07',
            'supported_locales' => '["en","ro"]',
        ]);

        $this->controller->exposePopulateSettingsFromForm($settings, $form);
        $manager->save($settings);

        $freshManager = new MktSettingsManager($this->storage, new SettingsHydrator());
        $reloaded = $freshManager->get(GeneralSettings::class);

        self::assertSame('Typed Site', $reloaded->site_name);
        self::assertFalse($reloaded->site_active);
        self::assertSame(5, $reloaded->max_items);
        self::assertSame(0.07, $reloaded->tax_rate);
        self::assertSame(['en', 'ro'], $reloaded->supported_locales);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Builds a TestDetailsForm initialised with the given settings, then overrides
     * the value of each named element so that populateSettingsFromForm sees the
     * provided values.
     *
     * @param array<string, string> $overrides  element name → raw string value
     */
    private function buildFormWithValues(GeneralSettings $settings, array $overrides): TestDetailsForm
    {
        $form = new TestDetailsForm();
        $form->setSettings($settings);
        $form->initialize();

        foreach ($overrides as $name => $value) {
            $element = $form->getElement($name);
            if ($element !== null) {
                $element->setValue($value);
            }
        }

        return $form;
    }
}
