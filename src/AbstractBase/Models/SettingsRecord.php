<?php

declare(strict_types=1);

namespace Marktic\Settings\AbstractBase\Models;

use Nip\Records\Record;

class SettingsRecord extends Record
{
    use \ByTIC\DataObjects\Behaviors\Timestampable\TimestampableTrait;

    /**
     * @var string
     */
    protected static $createTimestamps = ['created_at'];

    /**
     * @var string
     */
    protected static $updateTimestamps = ['updated_at'];

    public function getRegistry()
    {
        // TODO: Implement getRegistry() method.
    }
}
