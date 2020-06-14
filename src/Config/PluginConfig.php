<?php

namespace Jascha030\WP\Plugin\Core\Config;

use Jascha030\WP\Plugin\Core\Plugin;
use Jascha030\WP\Plugin\Core\PluginProvider;
use Jascha030\WP\Subscriptions\Exception\DoesNotImplementProviderException;
use Jascha030\WP\Subscriptions\Provider\SubscriptionProvider;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class PluginConfig
 *
 * @todo: Add Services
 *
 * @package Jascha030\WP\Plugin\Core\Config
 */
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

    public function pluginFile(string $file): void
    {
        $this->pluginFile = $file;
    }

    public function prefix(string $prefix = null): void
    {
        if (empty($prefix)) {
            $prefix = $this->generateUUID();
        }
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
        $this->pluginData = \get_file_data(
            $this->pluginFile,
            [
                'Name'        => 'Plugin Name',
                'Version'     => 'Version',
                'Description' => 'Description',
                'TextDomain'  => 'Text Domain',
            ]
        );

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

    protected function define(string $name, $value): void
    {
        $const = $this->pluginPrefix . strtoupper($name);

        if (! defined($const)) {
            Plugin::define($const, $value);
            $this->constants[strtolower($name)] = $value;
        }
    }

    protected function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}