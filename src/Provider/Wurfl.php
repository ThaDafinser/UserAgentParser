<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;
use Wurfl\CustomDevice;
use Wurfl\Manager as WurflManager;

class Wurfl extends AbstractProvider
{
    protected $defaultValues = [
        'Generic',
    ];

    /**
     *
     * @var WurflManager
     */
    private $parser;

    /**
     *
     * @param WurflManager $parser
     */
    public function __construct(WurflManager $parser)
    {
        $this->setParser($parser);
    }

    public function getName()
    {
        return 'Wurfl';
    }

    public function getComposerPackageName()
    {
        return 'mimmi20/wurfl';
    }

    public function getVersion()
    {
        return $this->getParser()->getWurflInfo()->version;
    }

    /**
     *
     * @param WurflManager $parser
     */
    public function setParser(WurflManager $parser)
    {
        $this->parser = $parser;
    }

    /**
     *
     * @return WurflManager
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     *
     * @param  CustomDevice $device
     * @return boolean
     */
    private function hasResult(CustomDevice $device)
    {
        if ($device->id !== null && $device->id != '' && $device->id !== 'generic') {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function isRealDeviceModel($value)
    {
        if ($this->isRealResult($value) !== true) {
            return false;
        }

        $value = (string) $value;

        $defaultValues = [
            'Android',
            'Firefox',
            'unrecognized',
            'Windows Mobile',
            'Windows Phone',
            'Windows RT',
        ];

        foreach ($defaultValues as $defaultValue) {
            if (substr($value, 0, strlen($defaultValue)) == $defaultValue) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @param Model\Browser $browser
     * @param CustomDevice  $deviceRaw
     */
    private function hydrateBrowser(Model\Browser $browser, CustomDevice $deviceRaw)
    {
        $browser->setName($deviceRaw->getVirtualCapability('advertised_browser'));
        $browser->getVersion()->setComplete($deviceRaw->getVirtualCapability('advertised_browser_version'));
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param CustomDevice          $deviceRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, CustomDevice $deviceRaw)
    {
        $os->setName($deviceRaw->getVirtualCapability('advertised_device_os'));
        $os->getVersion()->setComplete($deviceRaw->getVirtualCapability('advertised_device_os_version'));
    }

    /**
     *
     * @param Model\UserAgent $device
     * @param CustomDevice    $deviceRaw
     */
    private function hydrateDevice(Model\Device $device, CustomDevice $deviceRaw)
    {
        if ($deviceRaw->getVirtualCapability('is_full_desktop') !== 'true') {
            if ($this->isRealDeviceModel($deviceRaw->getCapability('model_name')) === true) {
                $device->setModel($deviceRaw->getCapability('model_name'));
            }

            if ($this->isRealResult($deviceRaw->getCapability('brand_name')) === true) {
                $device->setBrand($deviceRaw->getCapability('brand_name'));
            }

            if ($deviceRaw->getVirtualCapability('is_mobile') === 'true') {
                $device->setIsMobile(true);
            }

            if ($deviceRaw->getVirtualCapability('is_touchscreen') === 'true') {
                $device->setIsTouch(true);
            }
        }

        // @see the list of all types http://web.wurfl.io/
        $device->setType($deviceRaw->getVirtualCapability('form_factor'));
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();

        $deviceRaw = $parser->getDeviceForUserAgent($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($deviceRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw([
            'virtual' => $deviceRaw->getAllVirtualCapabilities(),
            'all'     => $deviceRaw->getAllCapabilities(),
        ]);

        /*
         * Bot detection
         */
        if ($deviceRaw->getVirtualCapability('is_robot') === 'true') {
            $bot = $result->getBot();
            $bot->setIsBot(true);

            // brand_name seems to be always google, so dont use it

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $deviceRaw);
        // renderingEngine not available
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $deviceRaw);
        $this->hydrateDevice($result->getDevice(), $deviceRaw);

        return $result;
    }
}
