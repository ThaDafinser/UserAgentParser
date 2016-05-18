<?php
namespace UserAgentParser\Provider;

use BrowscapPHP\Browscap;
use DateTime;
use stdClass;
use UserAgentParser\Exception\InvalidArgumentException;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Model;

/**
 * Abstraction for all browscap types
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://github.com/browscap/browscap-php
 */
abstract class AbstractBrowscap extends AbstractProvider
{
    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/browscap/browscap-php';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'browscap/browscap-php';

    protected $defaultValues = [

        'general' => [
            '/^unknown$/i',
        ],

        'browser' => [
            'name' => [
                '/^Default Browser$/i',
            ],
        ],

        'device' => [
            'model' => [
                '/^general/i',
                '/desktop$/i',
            ],
        ],

        'bot' => [
            'name' => [
                '/^General Crawlers/i',
                '/^Generic/i',
            ],
        ],
    ];

    /**
     *
     * @var Browscap
     */
    private $parser;

    public function __construct(Browscap $parser, $expectedType = '')
    {
        $this->parser = $parser;

        if ($parser->getCache()->getType() === null) {
            throw new InvalidArgumentException('You need to warm-up the cache first to use this provider');
        }

        if ($expectedType !== $parser->getCache()->getType()) {
            throw new InvalidArgumentException('Expected the "' . $expectedType . '" data file. Instead got the "' . $parser->getCache()->getType() . '" data file');
        }
    }

    public function getVersion()
    {
        return $this->getParser()
            ->getCache()
            ->getVersion();
    }

    public function getUpdateDate()
    {
        $releaseDate = $this->getParser()
            ->getCache()
            ->getReleaseDate();

        return DateTime::createFromFormat('D, d M Y H:i:s O', $releaseDate);
    }

    /**
     *
     * @return Browscap
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     *
     * @param stdClass $resultRaw
     *
     * @return bool
     */
    private function hasResult(stdClass $resultRaw)
    {
        if (isset($resultRaw->browser) && $this->isRealResult($resultRaw->browser, 'browser', 'name') === true) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param  stdClass $resultRaw
     * @return boolean
     */
    private function isBot(stdClass $resultRaw)
    {
        if (isset($resultRaw->crawler) && $resultRaw->crawler === true) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Bot $bot
     * @param stdClass  $resultRaw
     */
    private function hydrateBot(Model\Bot $bot, stdClass $resultRaw)
    {
        $bot->setIsBot(true);

        if (isset($resultRaw->browser)) {
            $bot->setName($this->getRealResult($resultRaw->browser, 'bot', 'name'));
        }

        if (isset($resultRaw->issyndicationreader) && $resultRaw->issyndicationreader === true) {
            $bot->setType('RSS');
        } elseif (isset($resultRaw->browser_type)) {
            $bot->setType($this->getRealResult($resultRaw->browser_type));
        }
    }

    /**
     *
     * @param Model\Browser $browser
     * @param stdClass      $resultRaw
     */
    private function hydrateBrowser(Model\Browser $browser, stdClass $resultRaw)
    {
        if (isset($resultRaw->browser)) {
            $browser->setName($this->getRealResult($resultRaw->browser, 'browser', 'name'));
        }

        if (isset($resultRaw->version)) {
            $browser->getVersion()->setComplete($this->getRealResult($resultRaw->version));
        }
    }

    /**
     *
     * @param Model\RenderingEngine $engine
     * @param stdClass              $resultRaw
     */
    private function hydrateRenderingEngine(Model\RenderingEngine $engine, stdClass $resultRaw)
    {
        if (isset($resultRaw->renderingengine_name)) {
            $engine->setName($this->getRealResult($resultRaw->renderingengine_name));
        }

        if (isset($resultRaw->renderingengine_version)) {
            $engine->getVersion()->setComplete($this->getRealResult($resultRaw->renderingengine_version));
        }
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param stdClass              $resultRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, stdClass $resultRaw)
    {
        if (isset($resultRaw->platform)) {
            $os->setName($this->getRealResult($resultRaw->platform));
        }

        if (isset($resultRaw->platform_version)) {
            $os->getVersion()->setComplete($this->getRealResult($resultRaw->platform_version));
        }
    }

    /**
     *
     * @param Model\UserAgent $device
     * @param stdClass        $resultRaw
     */
    private function hydrateDevice(Model\Device $device, stdClass $resultRaw)
    {
        if (isset($resultRaw->device_name)) {
            $device->setModel($this->getRealResult($resultRaw->device_name, 'device', 'model'));
        }

        if (isset($resultRaw->device_brand_name)) {
            $device->setBrand($this->getRealResult($resultRaw->device_brand_name));
        }

        if (isset($resultRaw->device_type)) {
            $device->setType($this->getRealResult($resultRaw->device_type));
        }

        if (isset($resultRaw->ismobiledevice) && $this->isRealResult($resultRaw->ismobiledevice) === true && $resultRaw->ismobiledevice === true) {
            $device->setIsMobile(true);
        }

        if (isset($resultRaw->device_pointing_method) && $resultRaw->device_pointing_method == 'touchscreen') {
            $device->setIsTouch(true);
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();

        /* @var $resultRaw \stdClass */
        $resultRaw = $parser->getBrowser($userAgent);

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
         * Bot detection (does only work with full_php_browscap.ini)
         */
        if ($this->isBot($resultRaw) === true) {
            $this->hydrateBot($result->getBot(), $resultRaw);

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $resultRaw);
        $this->hydrateRenderingEngine($result->getRenderingEngine(), $resultRaw);
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultRaw);
        $this->hydrateDevice($result->getDevice(), $resultRaw);

        return $result;
    }
}
