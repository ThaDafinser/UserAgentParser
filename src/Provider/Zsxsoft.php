<?php
namespace UserAgentParser\Provider;

use UserAgent;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

/**
 * Abstraction for zsxsoft/php-useragent
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://github.com/zsxsoft/php-useragent
 */
class Zsxsoft extends AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'Zsxsoft';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/zsxsoft/php-useragent';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'zsxsoft/php-useragent';

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
            'type'     => false,
            'isMobile' => false,
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => false,
            'name'  => false,
            'type'  => false,
        ],
    ];

    protected $defaultValues = [

        'general' => [
            '/^Unknown$/i',
        ],

        'browser' => [
            'name' => [
                '/^Mozilla Compatible$/i',
            ],
        ],

        'device' => [
            'model' => [
                '/^Browser$/i',
                '/^Android$/i',
            ],
        ],
    ];

    private $parser;

    /**
     *
     * @param  UserAgent                 $parser
     * @throws PackageNotLoadedException
     */
    public function __construct(UserAgent $parser = null)
    {
        if ($parser === null && ! file_exists('vendor/' . $this->getPackageName() . '/composer.json')) {
            throw new PackageNotLoadedException('You need to install the package ' . $this->getPackageName() . ' to use this provider');
        }

        $this->parser = $parser;
    }

    /**
     *
     * @return UserAgent
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $this->parser = new UserAgent();

        return $this->parser;
    }

    /**
     *
     * @param array $browser
     * @param array $os
     * @param array $device
     *
     * @return bool
     */
    private function hasResult(array $browser, array $os, array $device)
    {
        if (isset($browser['name']) && $this->isRealResult($browser['name'], 'browser', 'name')) {
            return true;
        }

        if (isset($os['name']) && $this->isRealResult($os['name'])) {
            return true;
        }

        if (isset($device['brand']) && $this->isRealResult($device['brand'])) {
            return true;
        }

        if (isset($device['model']) && $this->isRealResult($device['model'], 'device', 'model')) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Browser $browser
     * @param array         $browserRaw
     */
    private function hydrateBrowser(Model\Browser $browser, array $browserRaw)
    {
        if (isset($browserRaw['name']) && $this->isRealResult($browserRaw['name'], 'browser', 'name') === true) {
            $browser->setName($browserRaw['name']);
        }

        if (isset($browserRaw['version']) && $this->isRealResult($browserRaw['version']) === true) {
            $browser->getVersion()->setComplete($browserRaw['version']);
        }
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param array                 $osRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, array $osRaw)
    {
        if (isset($osRaw['name']) && $this->isRealResult($osRaw['name']) === true) {
            $os->setName($osRaw['name']);
        }

        if (isset($osRaw['version']) && $this->isRealResult($osRaw['version']) === true) {
            $os->getVersion()->setComplete($osRaw['version']);
        }
    }

    /**
     *
     * @param Model\Device $device
     * @param array        $deviceRaw
     */
    private function hydrateDevice(Model\Device $device, array $deviceRaw)
    {
        if (isset($deviceRaw['model']) && $this->isRealResult($deviceRaw['model'], 'device', 'model') === true) {
            $device->setModel($deviceRaw['model']);
        }

        if (isset($deviceRaw['brand']) && $this->isRealResult($deviceRaw['brand']) === true) {
            $device->setBrand($deviceRaw['brand']);
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();
        $parser->analyze($userAgent);

        $browser  = $parser->browser;
        $os       = $parser->os;
        $device   = $parser->device;
        $platform = $parser->platform;

        /*
         * No result found?
         */
        if ($this->hasResult($browser, $os, $device) !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw([
            'browser'  => $browser,
            'os'       => $os,
            'device'   => $device,
            'platform' => $platform,
        ]);

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $browser);
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $os);
        $this->hydrateDevice($result->getDevice(), $device);

        return $result;
    }
}
