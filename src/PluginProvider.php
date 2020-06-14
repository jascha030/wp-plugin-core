<?php

namespace Jascha030\WP\Plugin\Core;

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
        string $name,
        array $styles,
        array $scripts,
        string $minWpVersion = '5.0.0'
    ) {
        $this->pluginName       = $name;
        $this->stylesheets      = $styles;
        $this->scripts          = $scripts;
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

        if (! empty($this->styleSheets) || ! empty($this->scripts)) {
            static::$actions['wp_enqueue_scripts'] = [['enqueueScripts'], ['enqueueStyles']];
        }
    }

    final public function notices(): void
    {
        if ($this->showNotices) {
            foreach ($this->notices as $notice) {
                $notice->printNotice();
            }
        }
    }

    public function enqueueScripts(): void
    {
        foreach ($this->scripts as $handle => $script) {
            $src      = pluginUrl() . $script[0];
            $deps     = $script[1] ?? [];
            $ver      = $script[2] ?? null;
            $inFooter = $script[3] ?? false;

            wp_enqueue_script($handle, $src, $deps, $ver, $inFooter);
        }
    }

    public function enqueueStyles(): void
    {
        foreach ($this->stylesheets as $handle => $styleSheet) {
            $src = pluginUrl() . $styleSheet;
            wp_enqueue_style($handle, $src);
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