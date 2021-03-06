<?php

namespace Jascha030\WP\Plugin\Core;

use Jascha030\WP\Plugin\Core\Config\PluginConfig;
use Jascha030\WP\Subscriptions\Exception\DoesNotImplementProviderException;
use Jascha030\WP\Subscriptions\Exception\InvalidArgumentException;
use Jascha030\WP\Subscriptions\Runnable\Runnable;
use Jascha030\WP\Subscriptions\Shared\Container\Container;
use Jascha030\WP\Subscriptions\Shared\Container\WordpressSubscriptionContainer;

/**
 * Class Plugin
 *
 * @package Jascha030\WP\Plugin\Core
 */
class Plugin extends Container implements Runnable
{
    protected static $instance;

    protected $pluginConfig;

    protected $pluginFile;

    protected $pluginDir;

    protected $providerClass;

    protected $ran = false;

    protected $error;

    public function __construct(string $file)
    {
        $this->pluginFile = $file;
        $this->pluginDir  = plugin_dir_path($this->pluginFile);

        try {
            $this->bootstrapPlugin();
            $this->registerProviders();
        } catch (InvalidArgumentException $e) {
            // todo: error handling / logging
        } catch (DoesNotImplementProviderException $e) {
            // todo: error handling / logging
        }

        static::$instance = $this;
    }

    public static function define(string $name, $value): void
    {
        if (! defined($name)) {
            define($name, $value);
        }
    }

    /**
     * @return mixed
     */
    public function getPluginFile()
    {
        return $this->pluginFile;
    }

    public function run(): void
    {
        if ($this->ran) {
            return;
        }

        try {
            // Hook all wordpress plugin actions / filters
            WordpressSubscriptionContainer::getInstance()->run();
            $this->ran = true;
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }

    public function getPluginProvider(): PluginProvider
    {
        return $this->resolve($this->providerClass);
    }

    protected function setPluginProvider(): void
    {
        $provider            = $this->pluginConfig->getMain();
        $this->providerClass = get_class($provider);

        $this->bind($this->providerClass, $provider);
    }

    /**
     * @throws \Jascha030\WP\Subscriptions\Exception\InvalidArgumentException
     */
    protected function bootstrapPlugin(): void
    {
        $bootstrapFile = require $this->pluginDir . '/bootstrap.php';

        if (! $bootstrapFile instanceof PluginConfig) {
            throw new InvalidArgumentException("Wrong bootstrap directory: {$this->pluginDir}");
        }

        $this->pluginConfig = $bootstrapFile;
        $this->pluginConfig->run();
        $this->setPluginProvider();
    }

    /**
     * @throws \Jascha030\WP\Subscriptions\Exception\DoesNotImplementProviderException
     */
    protected function registerProviders(): void
    {
        $subscriptionContainer = WordpressSubscriptionContainer::getInstance();
        $providers             = $this->pluginConfig->getProviders();

        $subscriptionContainer->register($this->providerClass, $this->getPluginProvider());

        foreach ($providers as $provider) {
            $subscriptionContainer->register($provider);
        }
    }
}

function pluginDir()
{
    return \plugin_dir_path(Plugin::getInstance()->getPluginFile());
}

function pluginUrl()
{
    return \plugin_dir_url(Plugin::getInstance()->getPluginFile());
}
