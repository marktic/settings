<?php

declare(strict_types=1);

namespace Marktic\Settings\Utility;

class PathsHelpers
{
    public static function basePath(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function config(string $path = ''): string
    {
        return static::basePath() . '/config' . $path;
    }

    public static function resources(string $path = ''): string
    {
        return static::basePath() . '/resources' . $path;
    }

    public static function lang(string $path = ''): string
    {
        return static::resources() . '/lang' . $path;
    }

    public static function modules(string $path): string
    {
        return static::basePath() . '/src/Bundle/Modules' . $path;
    }

    public static function viewsAdmin(): string
    {
        return static::modules('/Admin/views');
    }
}
