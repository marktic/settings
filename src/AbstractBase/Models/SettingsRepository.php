<?php

declare(strict_types=1);

namespace Marktic\Settings\AbstractBase\Models;

use Nip\Records\RecordManager;

class SettingsRepository extends RecordManager
{
    use \ByTIC\Records\Behaviors\I18n\I18nRecordsTrait;
    use \ByTIC\Records\Behaviors\HasForms\HasFormsRecordsTrait;
    use \ByTIC\DataObjects\Behaviors\Timestampable\TimestampableManagerTrait;

    protected function generateController(): string
    {
        if (\defined('static::CONTROLLER')) {
            return static::CONTROLLER;
        }

        return $this->getTable();
    }
}
