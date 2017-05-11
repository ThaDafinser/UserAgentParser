<?php
namespace UserAgentParser\Provider;

use BrowserDetector\Detector;
use Psr\Log\NullLogger;
use Psr6NullCache\Adapter\MemoryCacheItemPool;
use UaResult\Browser\BrowserInterface;
use UaResult\Device\DeviceInterface;
use UaResult\Engine\EngineInterface;
use UaResult\Os\OsInterface;
use UaResult\Result\Result;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

/**
 * Abstraction for mimmi20/browser-detector
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://github.com/donatj/PhpUserAgent
 */
class Mimmi20BrowserDetector extends AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'Mimmi20BrowserDetector';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/mimmi20/BrowserDetector';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'mimmi20/browser-detector';

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
            '/^unknown$/i',
        ],
    ];

    /**
     *
     * @var Detector
     */
    private $parser;

    /**
     *
     * @param  Detector                  $parser
     * @throws PackageNotLoadedException
     */
    public function __construct(Detector $parser = null)
    {
        if ($parser === null) {
            $this->checkIfInstalled();
        }

        $this->parser = $parser;
    }

    /**
     *
     * @return Detector
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $cache  = new MemoryCacheItemPool();
        $logger = new NullLogger();

        $this->parser = new Detector($cache, $logger);

        return $this->parser;
    }

    /**
     *
     * @param Result $result
     *
     * @return bool
     */
    private function hasResult(Result $result)
    {
        if ($this->isRealResult($result->getBrowser()
            ->getType()
            ->getType())) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Bot        $bot
     * @param BrowserInterface $browserRaw
     */
    private function hydrateBot(Model\Bot $bot, BrowserInterface $browserRaw)
    {
        $bot->setIsBot(true);
        $bot->setName($this->getRealResult($browserRaw->getName()));
    }

    /**
     *
     * @param Model\Browser    $browser
     * @param BrowserInterface $browserRaw
     */
    private function hydrateBrowser(Model\Browser $browser, BrowserInterface $browserRaw)
    {
        $browser->setName($this->getRealResult($browserRaw->getName()));
        $browser->getVersion()->setComplete($this->getRealResult($browserRaw->getVersion()
            ->getVersion()));
    }

    /**
     *
     * @param Model\RenderingEngine $engine
     * @param EngineInterface       $engineRaw
     */
    private function hydrateRenderingEngine(Model\RenderingEngine $engine, EngineInterface $engineRaw)
    {
        $engine->setName($this->getRealResult($engineRaw->getName()));
        $engine->getVersion()->setComplete($this->getRealResult($engineRaw->getVersion()->getVersion()));
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param OsInterface           $osRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, OsInterface $osRaw)
    {
        $os->setName($this->getRealResult($osRaw->getName()));
        $os->getVersion()->setComplete($this->getRealResult($osRaw->getVersion()->getVersion()));
    }

    /**
     *
     * @param Model\UserAgent $device
     * @param DeviceInterface $deviceRaw
     */
    private function hydrateDevice(Model\Device $device, DeviceInterface $deviceRaw)
    {
        $device->setModel($this->getRealResult($deviceRaw->getDeviceName()));
        $device->setBrand($this->getRealResult($deviceRaw->getBrand()->getBrandName()));
        $device->setType($this->getRealResult($deviceRaw->getType()->getName()));

        if ($deviceRaw->getPointingMethod() === 'touchscreen') {
            $device->setIsTouch(true);
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $headers['HTTP_USER_AGENT'] = $userAgent;

        $detector = $this->getParser();

        $parser = $detector->getBrowser($headers);

        /*
         * No result found?
         */
        if ($this->hasResult($parser) !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent($this->getName(), $this->getVersion());
        $result->setProviderResultRaw($parser->toArray(true));

        /*
         * Bot detection
         */
        if ($parser->getBrowser()->getType()->getType() === 'bot') {
            $this->hydrateBot($result->getBot(), $parser->getBrowser());

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $parser->getBrowser());
        $this->hydrateRenderingEngine($result->getRenderingEngine(), $parser->getEngine());
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $parser->getOs());
        $this->hydrateDevice($result->getDevice(), $parser->getDevice());

        return $result;
    }
}
