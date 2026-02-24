<?php

declare(strict_types=1);

namespace Marktic\Settings\AbstractBase\Models;

use Nip\Records\RecordManager;

class SettingsRepository extends RecordManager
{
    protected function generateController(): string
    {
        if (\defined('static::CONTROLLER')) {
            return static::CONTROLLER;
        }

        return $this->getTable();
    }
}
