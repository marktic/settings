<?php

declare(strict_types=1);

namespace Marktic\Settings\Utility;

use Nip\View\View;

class ViewUtility
{
    public const NAMESPACE = 'MktSettings';

    public static function registerAdminPaths(View $view): void
    {
        $path = PathsHelpers::viewsAdmin();
        $view->addPath($path, self::NAMESPACE);
        $view->addPath($path);
    }
}
