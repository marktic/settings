<?php

use Nip\Container\Container;

require dirname(__DIR__) . '/vendor/autoload.php';

Container::setInstance(new Container());

$container = Container::getInstance();
$container->set('settings', new \Marktic\Settings\Settings\Models\Settings());
