<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Hydrator;

use Marktic\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Tests\AbstractTest;
use Marktic\Settings\Tests\Fixtures\Settings\Feature;
use Marktic\Settings\Tests\Fixtures\Settings\SelectSettings;
use Marktic\Settings\Tests\Fixtures\Settings\Theme;

class SettingsHydratorSelectTest extends AbstractTest
{
    private SettingsHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = new SettingsHydrator();
    }

    public function testExtractEnumPropertyAsSelectType(): void
    {
        $settings = new SelectSettings();
        $settings->theme = Theme::Dark;

        $dtos = $this->hydrator->extract($settings);

        $themeDto = $this->findDtoByName($dtos, 'theme');
        self::assertNotNull($themeDto);
        self::assertSame(SettingType::Select, $themeDto->type);
        self::assertSame('dark', $themeDto->value);
    }

    public function testHydrateEnumPropertyRestoresEnumInstance(): void
    {
        $settings = new SelectSettings();

        $dto = new SettingDto();
        $dto->name = 'theme';
        $dto->type = SettingType::Select;
        $dto->value = 'dark';

        $this->hydrator->hydrate($settings, [$dto]);

        self::assertSame(Theme::Dark, $settings->theme);
    }

    public function testHydrateEnumPropertyWithInvalidValueLeavesRawString(): void
    {
        $settings = new SelectSettings();

        $dto = new SettingDto();
        $dto->name = 'theme';
        $dto->type = SettingType::Select;
        $dto->value = 'invalid_value';

        $this->hydrator->hydrate($settings, [$dto]);

        // tryFrom() returns null; hydrator falls back to raw string
        self::assertSame('invalid_value', $settings->theme);
    }

    public function testExtractAndHydrateRoundTrip(): void
    {
        $original = new SelectSettings();
        $original->theme = Theme::System;

        $dtos = $this->hydrator->extract($original);

        $restored = new SelectSettings();
        $this->hydrator->hydrate($restored, $dtos);

        self::assertSame(Theme::System, $restored->theme);
    }

    // ── Radio ─────────────────────────────────────────────────────────────────

    public function testExtractRadioPropertyAsRadioType(): void
    {
        $settings = new SelectSettings();
        $settings->preferred_theme = 'dark';

        $dtos = $this->hydrator->extract($settings);

        $dto = $this->findDtoByName($dtos, 'preferred_theme');
        self::assertNotNull($dto);
        self::assertSame(SettingType::Radio, $dto->type);
        self::assertSame('dark', $dto->value);
    }

    public function testHydrateRadioPropertyRestoresString(): void
    {
        $settings = new SelectSettings();

        $dto = new SettingDto();
        $dto->name = 'preferred_theme';
        $dto->type = SettingType::Radio;
        $dto->value = 'system';

        $this->hydrator->hydrate($settings, [$dto]);

        self::assertSame('system', $settings->preferred_theme);
    }

    // ── MultiSelect ───────────────────────────────────────────────────────────

    public function testExtractArrayPropertyWithOptionsAsMultiSelectType(): void
    {
        $settings = new SelectSettings();
        $settings->active_features = ['dark_mode', 'newsletter'];

        $dtos = $this->hydrator->extract($settings);

        $dto = $this->findDtoByName($dtos, 'active_features');
        self::assertNotNull($dto);
        self::assertSame(SettingType::MultiSelect, $dto->type);
        self::assertSame('["dark_mode","newsletter"]', $dto->value);
    }

    public function testHydrateMultiSelectPropertyRestoresArray(): void
    {
        $settings = new SelectSettings();

        $dto = new SettingDto();
        $dto->name = 'active_features';
        $dto->type = SettingType::MultiSelect;
        $dto->value = '["dark_mode","two_factor"]';

        $this->hydrator->hydrate($settings, [$dto]);

        self::assertSame(['dark_mode', 'two_factor'], $settings->active_features);
    }

    public function testExtractMultiSelectRoundTrip(): void
    {
        $original = new SelectSettings();
        $original->active_features = ['newsletter', 'two_factor'];

        $dtos = $this->hydrator->extract($original);

        $restored = new SelectSettings();
        $this->hydrator->hydrate($restored, $dtos);

        self::assertSame(['newsletter', 'two_factor'], $restored->active_features);
    }

    public function testExtractEmptyMultiSelectEncodesEmptyArray(): void
    {
        $settings = new SelectSettings();
        $settings->active_features = [];

        $dtos = $this->hydrator->extract($settings);

        $dto = $this->findDtoByName($dtos, 'active_features');
        self::assertNotNull($dto);
        self::assertSame('[]', $dto->value);
    }

    /**
     * @param SettingDto[] $dtos
     */
    private function findDtoByName(array $dtos, string $name): ?SettingDto
    {
        foreach ($dtos as $dto) {
            if ($dto->name === $name) {
                return $dto;
            }
        }
        return null;
    }
}
