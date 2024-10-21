<?php

namespace UserAgentParser\Provider;

use BrowscapPHP\Browscap;

/**
 * Abstraction for Browscap full type.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @see https://github.com/browscap/browscap-php
 */
class BrowscapFull extends AbstractBrowscap
{
    /**
     * Name of the provider.
     *
     * @var string
     */
    protected $name = 'BrowscapFull';

    protected $detectionCapabilities = [
        'browser' => [
            'name' => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name' => true,
            'version' => true,
        ],

        'operatingSystem' => [
            'name' => true,
            'version' => true,
        ],

        'device' => [
            'model' => true,
            'brand' => true,
            'type' => true,
            'isMobile' => true,
            'isTouch' => true,
        ],

        'bot' => [
            'isBot' => true,
            'name' => true,
            'type' => true,
        ],
    ];

    public function __construct(Browscap $parser)
    {
        parent::__construct($parser, 'FULL');
    }
}
