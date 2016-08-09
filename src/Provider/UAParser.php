<?php
namespace UserAgentParser\Provider;

use UAParser\Parser;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

/**
 * Abstraction for ua-parser/uap-php
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://github.com/ua-parser/uap-php
 */
class UAParser extends AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'UAParser';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/ua-parser/uap-php';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'ua-parser/uap-php';

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
            'isBot' => true,
            'name'  => true,
            'type'  => false,
        ],
    ];

    protected $defaultValues = [

        'general' => [
            '/^Other$/i',

        ],

        'device' => [

            'brand' => [
                '/^Generic/i',
                '/^unknown$/i',
            ],

            'model' => [
                '/^generic$/i',
                '/^Smartphone$/i',
                '/^Feature Phone$/i',
                '/^iOS-Device$/i',
                '/^Tablet$/i',
                '/^Touch$/i',
                '/^Windows$/i',
                '/^Windows Phone$/i',
                '/^Android$/i',
            ],
        ],

        'bot' => [
            'name' => [
                '/^Other$/i',
                '/^crawler$/i',
                '/^robot$/i',
                '/^crawl$/i',
                '/^Spider$/i',
            ],
        ],
    ];

    private $parser;

    /**
     *
     * @param  Parser                    $parser
     * @throws PackageNotLoadedException
     */
    public function __construct(Parser $parser = null)
    {
        if ($parser === null) {
            $this->checkIfInstalled();
        }

        $this->parser = $parser;
    }

    /**
     *
     * @return Parser
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $this->parser = Parser::create();

        return $this->parser;
    }

    /**
     *
     * @param \UAParser\Result\Client $resultRaw
     *
     * @return bool
     */
    private function hasResult(\UAParser\Result\Client $resultRaw)
    {
        if ($this->isBot($resultRaw) === true) {
            return true;
        }

        if ($this->isRealResult($resultRaw->ua->family)) {
            return true;
        }

        if ($this->isRealResult($resultRaw->os->family)) {
            return true;
        }

        if ($this->isRealResult($resultRaw->device->model, 'device', 'model')) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param \UAParser\Result\Client $resultRaw
     *
     * @return bool
     */
    private function isBot(\UAParser\Result\Client $resultRaw)
    {
        if ($resultRaw->device->family === 'Spider') {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Bot               $bot
     * @param \UAParser\Result\Client $resultRaw
     */
    private function hydrateBot(Model\Bot $bot, \UAParser\Result\Client $resultRaw)
    {
        $bot->setIsBot(true);
        $bot->setName($this->getRealResult($resultRaw->ua->family, 'bot', 'name'));
    }

    /**
     *
     * @param Model\Browser              $browser
     * @param \UAParser\Result\UserAgent $uaRaw
     */
    private function hydrateBrowser(Model\Browser $browser, \UAParser\Result\UserAgent $uaRaw)
    {
        $browser->setName($this->getRealResult($uaRaw->family));

        $browser->getVersion()->setMajor($this->getRealResult($uaRaw->major));
        $browser->getVersion()->setMinor($this->getRealResult($uaRaw->minor));
        $browser->getVersion()->setPatch($this->getRealResult($uaRaw->patch));
    }

    /**
     *
     * @param Model\OperatingSystem            $os
     * @param \UAParser\Result\OperatingSystem $osRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, \UAParser\Result\OperatingSystem $osRaw)
    {
        $os->setName($this->getRealResult($osRaw->family));

        $os->getVersion()->setMajor($this->getRealResult($osRaw->major));
        $os->getVersion()->setMinor($this->getRealResult($osRaw->minor));
        $os->getVersion()->setPatch($this->getRealResult($osRaw->patch));
    }

    /**
     *
     * @param Model\UserAgent         $device
     * @param \UAParser\Result\Device $deviceRaw
     */
    private function hydrateDevice(Model\Device $device, \UAParser\Result\Device $deviceRaw)
    {
        $device->setModel($this->getRealResult($deviceRaw->model, 'device', 'model'));
        $device->setBrand($this->getRealResult($deviceRaw->brand, 'device', 'brand'));
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();

        /* @var $resultRaw \UAParser\Result\Client */
        $resultRaw = $parser->parse($userAgent);

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
        if ($this->isBot($resultRaw) === true) {
            $this->hydrateBot($result->getBot(), $resultRaw);

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $resultRaw->ua);
        // renderingEngine not available
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultRaw->os);
        $this->hydrateDevice($result->getDevice(), $resultRaw->device);

        return $result;
    }
}
