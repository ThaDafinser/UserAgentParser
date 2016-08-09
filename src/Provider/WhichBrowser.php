<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;
use WhichBrowser\Parser as WhichBrowserParser;

/**
 * Abstraction for whichbrowser/parser
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @author Niels Leenheer <niels@leenheer.nl>
 * @license MIT
 * @see https://github.com/WhichBrowser/Parser
 */
class WhichBrowser extends AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'WhichBrowser';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/WhichBrowser/Parser';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'whichbrowser/parser';

    protected $detectionCapabilities = [

        'browser' => [
            'name'    => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name'    => true,
            'version' => true,
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
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => true,
            'name'  => true,
            'type'  => false,
        ],
    ];

    /**
     * Used for unitTests mocking
     *
     * @var WhichBrowserParser
     */
    private $parser;

    /**
     * 
     * @throws PackageNotLoadedException
     */
    public function __construct()
    {
        $this->checkIfInstalled();
    }

    /**
     *
     * @param  array              $headers
     * @return WhichBrowserParser
     */
    public function getParser(array $headers)
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        return new WhichBrowserParser($headers);
    }

    /**
     *
     * @param Model\Bot                   $bot
     * @param \WhichBrowser\Model\Browser $browserRaw
     */
    private function hydrateBot(Model\Bot $bot, \WhichBrowser\Model\Browser $browserRaw)
    {
        $bot->setIsBot(true);
        $bot->setName($this->getRealResult($browserRaw->getName()));
    }

    /**
     *
     * @param Model\Browser               $browser
     * @param \WhichBrowser\Model\Browser $browserRaw
     */
    private function hydrateBrowser(Model\Browser $browser, \WhichBrowser\Model\Browser $browserRaw)
    {
        if ($this->isRealResult($browserRaw->getName(), 'browser', 'name') === true) {
            $browser->setName($browserRaw->getName());
            $browser->getVersion()->setComplete($this->getRealResult($browserRaw->getVersion()));

            return;
        }

        if (isset($browserRaw->using) && $browserRaw->using instanceof \WhichBrowser\Model\Using) {
            /* @var $usingRaw \WhichBrowser\Model\Using */
            $usingRaw = $browserRaw->using;

            if ($this->isRealResult($usingRaw->getName()) === true) {
                $browser->setName($usingRaw->getName());

                $browser->getVersion()->setComplete($this->getRealResult($usingRaw->getVersion()));
            }
        }
    }

    /**
     *
     * @param Model\RenderingEngine      $engine
     * @param \WhichBrowser\Model\Engine $engineRaw
     */
    private function hydrateRenderingEngine(Model\RenderingEngine $engine, \WhichBrowser\Model\Engine $engineRaw)
    {
        $engine->setName($this->getRealResult($engineRaw->getName()));
        $engine->getVersion()->setComplete($this->getRealResult($engineRaw->getVersion()));
    }

    /**
     *
     * @param Model\OperatingSystem  $os
     * @param \WhichBrowser\Model\Os $osRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, \WhichBrowser\Model\Os $osRaw)
    {
        $os->setName($this->getRealResult($osRaw->getName()));
        $os->getVersion()->setComplete($this->getRealResult($osRaw->getVersion()));
    }

    /**
     *
     * @param Model\Device               $device
     * @param \WhichBrowser\Model\Device $deviceRaw
     * @param WhichBrowserParser         $parser
     */
    private function hydrateDevice(Model\Device $device, \WhichBrowser\Model\Device $deviceRaw, WhichBrowserParser $parser)
    {
        $device->setModel($this->getRealResult($deviceRaw->getModel()));
        $device->setBrand($this->getRealResult($deviceRaw->getManufacturer()));
        $device->setType($this->getRealResult($parser->getType()));

        if ($parser->isMobile() === true) {
            $device->setIsMobile(true);
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $headers['User-Agent'] = $userAgent;

        $parser = $this->getParser($headers);

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
