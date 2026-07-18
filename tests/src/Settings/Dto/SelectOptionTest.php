<?php

declare(strict_types=1);

namespace Marktic\Settings\Tests\Settings\Dto;

use Marktic\Settings\Settings\Dto\SelectOption;
use Marktic\Settings\Tests\AbstractTest;
use Marktic\Settings\Tests\Fixtures\Settings\Theme;

class SelectOptionTest extends AbstractTest
{
    public function testFromCreatesOptionWithValueAndLabel(): void
    {
        $option = SelectOption::from('en', 'English');

        self::assertSame('en', $option->value);
        self::assertSame('English', $option->label);
    }

    public function testFromEnumUsesBackedValueAndProviderLabel(): void
    {
        $option = SelectOption::fromEnum(Theme::Dark);

        self::assertSame('dark', $option->value);
        self::assertSame('Dark Theme', $option->label);
    }

    public function testFromEnumFallsBackToCaseNameWhenNoProvider(): void
    {
        $option = SelectOption::fromEnum(PlainEnum::Foo);

        self::assertSame('Foo', $option->value);
        self::assertSame('Foo', $option->label);
    }

    public function testFromEnumUsesBackedValueForNonProviderEnum(): void
    {
        $option = SelectOption::fromEnum(SimpleBackedEnum::Active);

        self::assertSame('active', $option->value);
        self::assertSame('Active', $option->label);
    }
}

enum PlainEnum
{
    case Foo;
    case Bar;
}

enum SimpleBackedEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
