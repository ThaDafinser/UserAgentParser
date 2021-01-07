<?php

namespace UserAgentParser\Provider;

use BrowscapPHP\Browscap;

/**
 * Abstraction for Browscap lite type.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @see https://github.com/browscap/browscap-php
 */
class BrowscapLite extends AbstractBrowscap
{
    /**
     * Name of the provider.
     *
     * @var string
     */
    protected $name = 'BrowscapLite';

    protected $detectionCapabilities = [
        'browser' => [
            'name' => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name' => false,
            'version' => false,
        ],

        'operatingSystem' => [
            'name' => true,
            'version' => false,
        ],

        'device' => [
            'model' => false,
            'brand' => false,
            'type' => true,
            'isMobile' => true,
            'isTouch' => false,
        ],

        'bot' => [
            'isBot' => false,
            'name' => false,
            'type' => false,
        ],
    ];

    public function __construct(Browscap $parser)
    {
        parent::__construct($parser, 'LITE');
    }
}
