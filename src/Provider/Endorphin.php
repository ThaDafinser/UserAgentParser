<?php
namespace UserAgentParser\Provider;

use DeviceDetector\DeviceDetector;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

/**
 * Abstraction for piwik/device-detector
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://github.com/endorphin-studio/browser-detector
 */
class Endorphin extends AbstractProvider
{

    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'Endorphin';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/endorphin-studio/browser-detector';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'endorphin-studio/browser-detector';

    protected $detectionCapabilities = [
        
        'browser' => [
            'name' => true,
            'version' => true
        ],
        
        'renderingEngine' => [
            'name' => true,
            'version' => false
        ],
        
        'operatingSystem' => [
            'name' => true,
            'version' => true
        ],
        
        'device' => [
            'model' => true,
            'brand' => true,
            'type' => true,
            'isMobile' => true,
            'isTouch' => true
        ],
        
        'bot' => [
            'isBot' => true,
            'name' => true,
            'type' => true
        ]
    ];

    /**
     *
     * @throws PackageNotLoadedException
     */
    public function __construct()
    {
        if (! file_exists('vendor/' . $this->getPackageName() . '/composer.json')) {
            throw new PackageNotLoadedException('You need to install the package ' . $this->getPackageName() . ' to use this provider');
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $resultRaw = \EndorphinStudio\Detector\Detector::Analyse($userAgent);
        
        var_dump($resultRaw);
        exit();
        
        /*
         * No result found?
         */
        if ($parser->isDetected() !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }
        
        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($parser->toArray());
        
        /*
         * Bot detection
         */
        if ($parser->getType() === 'bot') {
            $this->hydrateBot($result->getBot(), $parser->browser);
            
            return $result;
        }
        
        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $parser->browser);
        $this->hydrateRenderingEngine($result->getRenderingEngine(), $parser->engine);
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $parser->os);
        $this->hydrateDevice($result->getDevice(), $parser->device, $parser);
        
        return $result;
    }
}
