<?php

namespace Jascha030\WP\Plugin\Core;

use Jascha030\WP\Plugin\Core\Notice\AdminPluginNotice;
use Jascha030\WP\Subscriptions\Provider\ActionProvider;
use Jascha030\WP\Subscriptions\Runnable\Runnable;

class PluginProvider implements ActionProvider, Runnable
{
    protected static $actions = [];

    public $pluginName;

    public $minimumWpVersion;

    protected $notices = [];

    protected $showNotices = false;

    public function __construct(string $pluginName, string $minimumWpVersion)
    {
        $this->pluginName       = $pluginName;
        $this->minimumWpVersion = $minimumWpVersion;

        if (! $this->verifyWpVersion()) {
            $notice = new AdminPluginNotice(
                "The minimum Wordpress version required for {$this->pluginName} is {$this->minimumWpVersion}.",
                AdminPluginNotice::NOTICE_ERROR
            );

            $this->notices[]   = $notice;
            $this->showNotices = true;
        }
    }

    public function notices(): void
    {
        if ($this->showNotices) {
            foreach ($this->notices as $notice) {
                $notice->printNotice();
            }
        }
    }

    public function run(): void
    {
        $this->notices();
    }

    protected function verifyWpVersion(): bool
    {
        return ((int)get_bloginfo('version') >= (int)$this->minimumWpVersion);
    }
}