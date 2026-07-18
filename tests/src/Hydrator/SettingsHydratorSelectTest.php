<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Hydrator;

use Marktic\Settings\Hydrator\SettingsHydrator;
use Marktic\Settings\Settings\Dto\SettingDto;
use Marktic\Settings\Settings\Enums\SettingType;
use Marktic\Settings\Tests\AbstractTest;
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
