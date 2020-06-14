<?php

namespace Jascha030\WP\Plugin\Core\Config;

use Jascha030\WP\Plugin\Core\Plugin;
use Jascha030\WP\Plugin\Core\PluginProvider;
use Jascha030\WP\Subscriptions\Exception\DoesNotImplementProviderException;
use Jascha030\WP\Subscriptions\Provider\SubscriptionProvider;
use Symfony\Component\Dotenv\Dotenv;

class PluginConfig
{
    protected $constants = [];

    protected $pluginData = [];

    protected $pluginPrefix;

    protected $pluginFile;

    protected $providers = [];

    protected $stylesheets = [];

    protected $scripts = [];

    protected $useEnv = false;

    protected $envPath;

    protected $env;

    protected $mainPluginClass;

    public function prefix(string $prefix): void
    {
        $this->pluginPrefix = $prefix;
    }

    public function main(string $class): void
    {
        $this->mainPluginClass = $class;
    }

    public function constants(array $constants): void
    {
        $this->constants = $constants;
    }

    public function scripts(array $scripts): void
    {
        $this->scripts = $scripts;
    }

    public function styles(array $stylesheets): void
    {
        $this->stylesheets = $stylesheets;
    }

    public function providers(array $providers = []): void
    {
        foreach ($providers as $provider) {
            if (! is_subclass_of($provider, SubscriptionProvider::class)) {
                throw new DoesNotImplementProviderException($provider);
            }
        }

        $this->providers = $providers;
    }

    public function getConstant(string $key)
    {
        if (array_key_exists(strtolower($key), $this->constants)) {
            return $this->constants[$key];
        }

        return false;
    }

    public function enableEnv(string $path): void
    {
        $this->useEnv  = true;
        $this->envPath = $path;
    }

    public function getPluginData(): array
    {
        return $this->pluginData;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getMain(): PluginProvider
    {
        $class = $this->getMainClass();

        return new $class($this->getConstant('name'), $this->stylesheets, $this->scripts);
    }

    public function getMainClass(): string
    {
        return $this->mainPluginClass;
    }

    public function getEnv()
    {
        return $this->env;
    }

    public function run(): void
    {
        if (defined('ABSPATH') && function_exists('get_plugin_data')) {
            $this->pluginData = get_plugin_data($this->pluginFile);

            if ($this->useEnv) {
                $this->env = new Dotenv();
                $this->env->load($this->envPath);
            }

            $constants       = $this->constants;
            $this->constants = [];

            foreach ($constants as $key => $constant) {
                $this->define(strtoupper($key), $constant);
            }

            foreach ($this->pluginData as $key => $value) {
                $this->define(strtoupper($key), $value);
            }
        }
    }

    protected function define(string $name, $value): void
    {
        $const = $this->pluginPrefix . strtoupper($name);

        if (! defined($const)) {
            Plugin::define($const, $value);
            $this->constants[strtolower($name)] = $value;
        }
    }
}