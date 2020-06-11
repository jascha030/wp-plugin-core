<?php

namespace Jascha030\WP\Plugin\Core\Notice;

use Exception;

/**
 * Class AdminPluginNotice
 *
 * Represents wp-admin notice
 *
 * @package Jascha030\WP\Plugin\Plugin\Notice
 * @author Social Brothers
 *
 * Internal use
 * @developer Jascha
 */
class AdminPluginNotice
{
    public const NOTICE_ERROR = 0;
    public const NOTICE_WARNING = 1;
    public const NOTICE_SUCCESS = 2;
    public const NOTICE_INFO = 3;

    protected const NOTICE_TYPES = [
        self::NOTICE_ERROR,
        self::NOTICE_WARNING,
        self::NOTICE_SUCCESS,
        self::NOTICE_INFO
    ];

    public const CSS_CLASSES = [
        'notice-error',
        'notice-warning',
        'notice-success',
        'notice-info'
    ];

    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $type;

    /**
     * @var bool
     */
    private $dismissible;

    /**
     * AdminNotice constructor.
     *
     * @param string $message
     * @param int $type
     * @param bool $dismissible
     */
    public function __construct(string $message, int $type = 3, bool $dismissible = true)
    {
        $this->message     = $message;
        $this->type        = $type;
        $this->dismissible = $dismissible;
    }

    /**
     * @throws \Exception
     */
    public function printNotice(): void
    {
        $m = __($this->message);
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($this->getCssClass()), esc_html($m));
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getCssClass(): string
    {
        if (! in_array($this->type, static::NOTICE_TYPES)) {
            throw new Exception('Invalid value for notice type');
        }

        $class = 'notice ' . self::CSS_CLASSES[$this->type];
        $class .= ($this->dismissible) ? ' is-dismissible' : '';

        return $class;
    }
}
