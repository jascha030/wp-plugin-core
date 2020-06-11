<?php

namespace Jascha030\WP\Plugin\Core\Config;

use Jascha030\WP\Plugin\Core\Plugin;
use Jascha030\WP\Subscriptions\Exception\DoesNotImplementProviderException;
use Jascha030\WP\Subscriptions\Provider\SubscriptionProvider;
use Symfony\Component\Dotenv\Dotenv;

class PluginConfig
{
    protected const CONSTANTS_TO_SET = [
        'DIR',
        'URL',
        'DOMAIN',
        'DOMAIN_DIR',
        'VERSION',
        'NAME',
        'SLUG',
        'DB',
    ];

    protected $constants = [];

    protected $pluginData = [];

    protected $pluginPrefix;

    protected $pluginFile;

    protected $useEnv = false;

    protected $envPath;

    protected $env;

    protected $providers = [];

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

    public function providers(array $providers = []): void
    {
        foreach ($providers as $provider) {
            if (! is_subclass_of($provider, SubscriptionProvider::class)) {
                throw new DoesNotImplementProviderException($provider);
            }
        }

        $this->providers = $providers;
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

            foreach ($this->constants as $key => $const) {
                $this->define($key, $const);
            }

            foreach ($this->pluginData as $key => $value) {
                $this->define(strtoupper($key), $value);
            }
        }
    }

    protected function define(string $name, $value): void
    {
        if (! defined($this->pluginPrefix . $name)) {
            Plugin::define($this->pluginPrefix . $name, $value);
        }
    }
}