<?php

namespace Jascha030\WP\Plugin\Core;

use Jascha030\WP\Plugin\Core\Config\PluginConfig;
use Jascha030\WP\Plugin\Core\Notice\AdminPluginNotice;
use Jascha030\WP\Subscriptions\Provider\ActionProvider;

abstract class PluginProvider implements ActionProvider
{
    protected static $actions = [];

    public $pluginName;

    public $version;

    public $minimumWpVersion;

    protected $notices = [];

    protected $showNotices = false;

    protected $stylesheets;

    protected $scripts;

    public function __construct(
        PluginConfig $config,
        string $minWpVersion = '5.0.0'
    ) {
        $this->pluginName       = $config->getConstant('name');
        $this->version          = $config->getConstant('version');
        $this->minimumWpVersion = $minWpVersion;

        if (! $this->verifyWpVersion()) {
            $this->setNotice(
                new AdminPluginNotice(
                    "The minimum Wordpress version required for {$this->pluginName} is {$this->minimumWpVersion}.",
                    AdminPluginNotice::NOTICE_ERROR
                )
            );
        }

        static::$actions['admin_notices'] = 'notices';
    }

    final public function notices(): void
    {
        if ($this->showNotices) {
            foreach ($this->notices as $notice) {
                $notice->printNotice();
            }
        }
    }

    /**
     * Set notice to display in wp-admin
     *
     * @param \Jascha030\WP\Plugin\Core\Notice\AdminPluginNotice $notice
     */
    protected function setNotice(AdminPluginNotice $notice): void
    {
        if (! $this->showNotices) {
            $this->showNotices = true;
        }
        $this->notices[] = $notice;
    }

    protected function verifyWpVersion(): bool
    {
        return ((int)get_bloginfo('version') >= (int)$this->minimumWpVersion);
    }
}