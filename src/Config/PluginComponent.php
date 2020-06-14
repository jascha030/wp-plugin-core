<?php

namespace Jascha030\WP\Plugin\Core\Config;

use Jascha030\WP\Subscriptions\Runnable\Runnable;

abstract class PluginComponent implements Runnable
{
    protected $name;

    abstract public function run();
}