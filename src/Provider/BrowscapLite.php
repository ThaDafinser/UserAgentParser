<?php
namespace UserAgentParser\Provider;

use BrowscapPHP\Browscap;

class BrowscapLite extends AbstractBrowscap
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'BrowscapLite';

    protected $detectionCapabilities = [

        'browser' => [
            'name'    => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name'    => false,
            'version' => false,
        ],

        'operatingSystem' => [
            'name'    => true,
            'version' => false,
        ],

        'device' => [
            'model'    => false,
            'brand'    => false,
            'type'     => true,
            'isMobile' => true,
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => false,
            'name'  => false,
            'type'  => false,
        ],
    ];

    public function __construct(Browscap $parser)
    {
        parent::__construct($parser, 'LITE');
    }
}
