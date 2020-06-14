<?php

namespace Jascha030\WP\Plugin\Core\Config;

use Jascha030\WP\Subscriptions\Runnable\Runnable;

/**
 * Class PluginComponent
 *
 * @package Jascha030\WP\Plugin\Core\Config
 */
abstract class PluginComponent implements Runnable
{
    protected $name;

    abstract public function run();
}