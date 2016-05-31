<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception\InvalidArgumentException;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

/**
 * Abstraction for donatj/PhpUserAgent
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see http://www.detectright.com/
 */
class DetectRight extends AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'DetectRight';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'http://www.detectright.com/';

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
            'version' => true,
        ],

        'device' => [
            'model'    => true,
            'brand'    => true,
            'type'     => true,
            'isMobile' => false,
            'isTouch'  => true,
        ],

        'bot' => [
            'isBot' => true,
            'name'  => true,
            'type'  => false,
        ],
    ];

    protected $defaultValues = [
        'general' => [
            '/^UserAgent$/',
        ],
    ];
    
    private $dataFile;

    /**
     *
     * @param  string                    $dataFile
     * @throws PackageNotLoadedException
     * @throws InvalidArgumentException
     */
    public function __construct($dataFile)
    {
        if (! class_exists('\DetectRight')) {
            throw new PackageNotLoadedException('You need to download and include the package by hand from ' . $this->getHomepage() . ' to use this provider');
        }

        if (! file_exists($dataFile)) {
            throw new InvalidArgumentException('Data file not found ' . $dataFile);
        }

        $this->dataFile = $dataFile;
    }

    /**
     *
     * @return string
     */
    public function getDataFile()
    {
        return $this->dataFile;
    }

    /**
     *
     * @param Model\Bot $bot
     * @param array     $resultRaw
     */
    private function hydrateBot(Model\Bot $bot, $resultRaw)
    {
        $bot->setIsBot(true);

        if (isset($resultRaw['model_name'])) {
            $bot->setName($this->getRealResult($resultRaw['model_name']));
        }
    }

    /**
     *
     * @param Model\Browser $browser
     * @param array         $resultRaw
     */
    private function hydrateBrowser(Model\Browser $browser, array $resultRaw)
    {
        if (isset($resultRaw['mobile_browser'])) {
            $browser->setName($this->getRealResult($resultRaw['mobile_browser']));
        }
        if (isset($resultRaw['mobile_browser_version'])) {
            $browser->getVersion()->setComplete($this->getRealResult($resultRaw['mobile_browser_version']));
        }
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param array                 $resultRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, array $resultRaw)
    {
        if (isset($resultRaw['device_os'])) {
            $os->setName($this->getRealResult($resultRaw['device_os']));
        }
        if (isset($resultRaw['device_os_version'])) {
            $os->getVersion()->setComplete($this->getRealResult($resultRaw['device_os_version']));
        }
    }

    /**
     *
     * @param Model\Device $device
     * @param array        $resultRaw
     */
    private function hydrateDevice(Model\Device $device, array $resultRaw, $userAgent)
    {
        if (isset($resultRaw['model_name']) && $resultRaw['model_name'] !== $userAgent) {
            $device->setModel($this->getRealResult($resultRaw['model_name']));
        }
        if (isset($resultRaw['brand_name'])) {
            $device->setBrand($this->getRealResult($resultRaw['brand_name']));
        }
        if (isset($resultRaw['device_type'])) {
            $device->setType($this->getRealResult($resultRaw['device_type']));
        }

        if (isset($resultRaw['has_touchscreen']) && $resultRaw['has_touchscreen'] === 1) {
            $device->setIsTouch(true);
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        /*
         * Some settings
         */
        //\DetectRight::generateExceptionOnDeviceNotFound();
        \DetectRight::initialize('DRSQLite//' . realpath($this->getDataFile()));

        $headers['User-Agent'] = $userAgent;

        try {
            $resultRaw = \DetectRight::getProfileFromHeaders($headers);
        } catch (\DeviceNotFoundException $ex) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultRaw);

        /*
         * Bot detection
         */
        if (isset($resultRaw['device_type']) && $resultRaw['device_type'] === 'Bot') {
            $this->hydrateBot($result->getBot(), $resultRaw);

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $resultRaw);
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultRaw);
        $this->hydrateDevice($result->getDevice(), $resultRaw, $userAgent);

        return $result;
    }
}
