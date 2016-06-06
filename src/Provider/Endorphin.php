<?php
namespace UserAgentParser\Provider;

use EndorphinStudio\Detector;
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
            'name'    => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name'    => true,
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
            'isMobile' => true,
            'isTouch'  => true,
        ],

        'bot' => [
            'isBot' => true,
            'name'  => true,
            'type'  => true,
        ],
    ];

    protected $defaultValues = [

        'general' => [
            '/^N\\\\A$/i',
        ],
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

    /**
     *
     * @param Detector\DetectorResult $resultRaw
     *
     * @return bool
     */
    private function hasResult(Detector\DetectorResult $resultRaw)
    {
        if ($resultRaw->OS instanceof Detector\OS) {
            return true;
        }

        if ($resultRaw->Browser instanceof Detector\Browser) {
            return true;
        }

        if ($resultRaw->Device instanceof Detector\Device) {
            return true;
        }

        if ($resultRaw->Robot instanceof Detector\Robot) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Bot      $bot
     * @param Detector\Robot $resultRaw
     */
    private function hydrateBot(Model\Bot $bot, Detector\Robot $resultRaw)
    {
        $bot->setIsBot(true);
        $bot->setName($this->getRealResult($resultRaw->getName()));
    }

    /**
     *
     * @param Model\Browser    $browser
     * @param Detector\Browser $resultRaw
     */
    private function hydrateBrowser(Model\Browser $browser, Detector\Browser $resultRaw)
    {
        $browser->setName($this->getRealResult($resultRaw->getName()));
        $browser->getVersion()->setComplete($this->getRealResult($resultRaw->getVersion()));
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param Detector\OS           $resultRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, Detector\OS $resultRaw)
    {
        $os->setName($this->getRealResult($resultRaw->getName()));
        $os->getVersion()->setComplete($this->getRealResult($resultRaw->getVersion()));
    }

    /**
     *
     * @param Model\Device    $device
     * @param Detector\Device $resultRaw
     */
    private function hydrateDevice(Model\Device $device, Detector\Device $resultRaw)
    {
        $device->setModel($this->getRealResult($resultRaw->ModelName));
        $device->setType($this->getRealResult($resultRaw->getType()));
    }

    public function parse($userAgent, array $headers = [])
    {
        $resultRaw = \EndorphinStudio\Detector\Detector::analyse($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($resultRaw) !== true) {
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
        if ($resultRaw->Robot instanceof Detector\Robot) {
            $this->hydrateBot($result->getBot(), $resultRaw->Robot);

            return $result;
        }

        /*
         * hydrate the result
         */
        if ($resultRaw->Browser instanceof Detector\Browser) {
            $this->hydrateBrowser($result->getBrowser(), $resultRaw->Browser);
        }
        if ($resultRaw->OS instanceof Detector\OS) {
            $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultRaw->OS);
        }
        if ($resultRaw->Device instanceof Detector\Device) {
            $this->hydrateDevice($result->getDevice(), $resultRaw->Device);
        }

        return $result;
    }
}
