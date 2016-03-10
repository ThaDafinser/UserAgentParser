<?php
namespace UserAgentParser\Provider;

use Jenssegers\Agent\Agent;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

class JenssegersAgent extends AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'JenssegersAgent';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/jenssegers/agent';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'jenssegers/agent';

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
            'model'    => false,
            'brand'    => false,
            'type'     => false,
            'isMobile' => true,
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => true,
            'name'  => true,
            'type'  => false,
        ],
    ];

    protected $defaultValues = [

        'general' => [],

        'browser' => [
            'name' => [
                '/^GenericBrowser$/i',
            ],
        ],
    ];

    /**
     * Used for unitTests mocking
     *
     * @var Agent
     */
    private $parser;

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
     * @return Agent
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        return new Agent();
    }

    /**
     *
     * @param array $resultRaw
     *
     * @return bool
     */
    private function hasResult(array $resultRaw)
    {
        if ($resultRaw['isMobile'] === true || $resultRaw['isRobot'] === true) {
            return true;
        }

        if ($this->isRealResult($resultRaw['browserName']) === true || $this->isRealResult($resultRaw['osName']) === true || $this->isRealResult($resultRaw['botName']) === true) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Bot $bot
     * @param array     $browserRaw
     */
    private function hydrateBot(Model\Bot $bot, array $resultRaw)
    {
        $bot->setIsBot(true);

        if ($this->isRealResult($resultRaw['botName']) === true) {
            $bot->setName($resultRaw['botName']);
        }
    }

    /**
     *
     * @param Model\Browser $browser
     * @param array         $resultRaw
     */
    private function hydrateBrowser(Model\Browser $browser, array $resultRaw)
    {
        if ($this->isRealResult($resultRaw['browserName']) === true) {
            $browser->setName($resultRaw['browserName']);

            if ($this->isRealResult($resultRaw['browserVersion']) === true) {
                $browser->getVersion()->setComplete($resultRaw['browserVersion']);
            }
        }
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param array                 $resultRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, array $resultRaw)
    {
        if ($this->isRealResult($resultRaw['osName']) === true) {
            $os->setName($resultRaw['osName']);

            if ($this->isRealResult($resultRaw['osVersion']) === true) {
                $os->getVersion()->setComplete($resultRaw['osVersion']);
            }
        }
    }

    /**
     *
     * @param Model\Device $device
     * @param array        $resultRaw
     */
    private function hydrateDevice(Model\Device $device, array $resultRaw)
    {
        if ($resultRaw['isMobile'] === true) {
            $device->setIsMobile(true);
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();
        $parser->setHttpHeaders($headers);
        $parser->setUserAgent($userAgent);

        /*
         * Since Mobile_Detect to a regex comparison on every call
         * We cache it here for all checks and hydration
         */
        $browserName = $parser->browser();
        $osName      = $parser->platform();

        $resultCache = [
            'browserName'    => $browserName,
            'browserVersion' => $parser->version($browserName),

            'osName'    => $osName,
            'osVersion' => $parser->version($osName),

            'deviceModel' => $parser->device(),
            'isMobile'    => $parser->isMobile(),

            'isRobot' => $parser->isRobot(),
            'botName' => $parser->robot(),
        ];

        /*
         * No result found?
         */
        if ($this->hasResult($resultCache) !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultCache);

        /*
         * Bot detection
         */
        if ($resultCache['isRobot'] === true) {
            $this->hydrateBot($result->getBot(), $resultCache);

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $resultCache);
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultCache);
        $this->hydrateDevice($result->getDevice(), $resultCache);

        return $result;
    }
}
