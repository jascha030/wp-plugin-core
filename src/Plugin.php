<?php

namespace Jascha030\WP\Plugin\Core;

use Jascha030\WP\Plugin\Core\Config\PluginConfig;
use Jascha030\WP\Subscriptions\Exception\InvalidArgumentException;
use Jascha030\WP\Subscriptions\Runnable\Runnable;
use Jascha030\WP\Subscriptions\Shared\Container\Container;
use Jascha030\WP\Subscriptions\Shared\Container\WordpressSubscriptionContainer;

class Plugin extends Container implements Runnable
{
    protected $pluginConfig;

    protected $pluginDir;

    protected $mainClass;

    protected $ran = false;

    public function __construct(string $file)
    {
        $this->pluginDir = plugin_dir_path($file);
        $bootstrapFile   = require($this->pluginDir);

        if (! $bootstrapFile instanceof PluginConfig) {
            throw new InvalidArgumentException("Wrong bootstrap directory: {$this->pluginDir}");
        }

        $this->pluginConfig = $bootstrapFile;
        $this->pluginConfig->run();
    }

    public static function define(string $name, $value): void
    {
        if (! defined($name)) {
            define($name, $value);
        }
    }

    public function getConfig(): array
    {
        return $this->pluginConfig->getPluginData();
    }

    /**
     * @throws \Jascha030\WP\Subscriptions\Exception\DoesNotImplementProviderException
     */
    public function run(): void
    {
        if ($this->ran) {
            return;
        }

        $subscriptionContainer = WordpressSubscriptionContainer::getInstance();
        $providers             = $this->pluginConfig->getProviders();

        foreach ($providers as $provider) {
            $subscriptionContainer->register($provider);
        }

        $this->ran = true;
    }
}