<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Model;
use Wurfl\CustomDevice;
use Wurfl\Manager as WurflManager;

/**
 * Abstraction for mimmi20/wurfl
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://github.com/mimmi20/Wurfl
 */
class Wurfl extends AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'Wurfl';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/mimmi20/Wurfl';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'mimmi20/wurfl';

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
            'isMobile' => true,
            'isTouch'  => true,
        ],

        'bot' => [
            'isBot' => true,
            'name'  => false,
            'type'  => false,
        ],
    ];

    protected $defaultValues = [

        'general' => [],

        'operatingSystem' => [
            'name' => [
                '/^Unknown$/i',
                '/^en$/i',
                '/^en_US$/i',
                '/^Desktop$/i',
                '/^Mobile$/i',
                '/^Randomized by FreeSafeIP.com$/i',
            ],
        ],

        'device' => [

            'brand' => [
                '/^Generic$/i',
            ],

            'model' => [
                '/^Android/i',
                '/^Windows Phone/i',
                '/^Windows Mobile/i',
                '/^Firefox/i',
                '/^unrecognized/i',
                '/^Generic/i',
                '/^Disguised as Macintosh$/i',
                '/^Windows RT/i',
                '/^Tablet on Android$/i',
            ],
        ],
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
        $this->parser = $parser;
    }

    public function getVersion()
    {
        $version      = $this->getParser()->getWurflInfo()->version;

        preg_match("/\d+(?:\.\d+)+/", $version, $result);

        if (isset($result[0])) {
            return $result[0];
        }

        return;
    }

    public function getUpdateDate()
    {
        // 2015-10-16 11:09:44 -0400
        $lastUpdated = $this->getParser()->getWurflInfo()->lastUpdated;

        if ($lastUpdated == '') {
            return;
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s O', $lastUpdated);
        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date;
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
        $os->setName($this->getRealResult($deviceRaw->getVirtualCapability('advertised_device_os'), 'operatingSystem', 'name'));
        $os->getVersion()->setComplete($deviceRaw->getVirtualCapability('advertised_device_os_version'));
    }

    /**
     *
     * @param Model\UserAgent $device
     * @param CustomDevice    $deviceRaw
     */
    private function hydrateDevice(Model\Device $device, CustomDevice $deviceRaw)
    {
        // @see the list of all types http://web.wurfl.io/
        $device->setType($deviceRaw->getVirtualCapability('form_factor'));

        if ($deviceRaw->getVirtualCapability('is_full_desktop') === 'true' || $deviceRaw->getVirtualCapability('is_full_desktop') === true) {
            return;
        }

        $device->setModel($this->getRealResult($deviceRaw->getCapability('model_name'), 'device', 'model'));
        $device->setBrand($this->getRealResult($deviceRaw->getCapability('brand_name'), 'device', 'brand'));

        if ($deviceRaw->getVirtualCapability('is_mobile') === 'true' || $deviceRaw->getVirtualCapability('is_mobile') === true) {
            $device->setIsMobile(true);
        }

        if ($deviceRaw->getVirtualCapability('is_touchscreen') === 'true' || $deviceRaw->getVirtualCapability('is_touchscreen') === true) {
            $device->setIsTouch(true);
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();

        $deviceRaw = $parser->getDeviceForUserAgent($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($deviceRaw) !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
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
        if ($deviceRaw->getVirtualCapability('is_robot') === 'true' || $deviceRaw->getVirtualCapability('is_robot') === true) {
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
