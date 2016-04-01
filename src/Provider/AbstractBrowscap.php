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

        if (isset($resultRaw->browser) && $this->isRealResult($resultRaw->browser, 'bot', 'name') === true) {
            $bot->setName($resultRaw->browser);
        }

        if (isset($resultRaw->issyndicationreader) && $resultRaw->issyndicationreader === true) {
            $bot->setType('RSS');
        } elseif (isset($resultRaw->browser_type) && $this->isRealResult($resultRaw->browser_type) === true) {
            $bot->setType($resultRaw->browser_type);
        }
    }

    /**
     *
     * @param Model\Browser $browser
     * @param stdClass      $resultRaw
     */
    private function hydrateBrowser(Model\Browser $browser, stdClass $resultRaw)
    {
        if (isset($resultRaw->browser) && $this->isRealResult($resultRaw->browser, 'browser', 'name') === true) {
            $browser->setName($resultRaw->browser);
        }

        if (isset($resultRaw->version) && $this->isRealResult($resultRaw->version) === true) {
            $browser->getVersion()->setComplete($resultRaw->version);
        }
    }

    /**
     *
     * @param Model\RenderingEngine $engine
     * @param stdClass              $resultRaw
     */
    private function hydrateRenderingEngine(Model\RenderingEngine $engine, stdClass $resultRaw)
    {
        if (isset($resultRaw->renderingengine_name) && $this->isRealResult($resultRaw->renderingengine_name) === true) {
            $engine->setName($resultRaw->renderingengine_name);
        }

        if (isset($resultRaw->renderingengine_version) && $this->isRealResult($resultRaw->renderingengine_version) === true) {
            $engine->getVersion()->setComplete($resultRaw->renderingengine_version);
        }
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param stdClass              $resultRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, stdClass $resultRaw)
    {
        if (isset($resultRaw->platform) && $this->isRealResult($resultRaw->platform) === true) {
            $os->setName($resultRaw->platform);
        }

        if (isset($resultRaw->platform_version) && $this->isRealResult($resultRaw->platform_version) === true) {
            $os->getVersion()->setComplete($resultRaw->platform_version);
        }
    }

    /**
     *
     * @param Model\UserAgent $device
     * @param stdClass        $resultRaw
     */
    private function hydrateDevice(Model\Device $device, stdClass $resultRaw)
    {
        if (isset($resultRaw->device_name) && $this->isRealResult($resultRaw->device_name, 'device', 'model') === true) {
            $device->setModel($resultRaw->device_name);
        }

        if (isset($resultRaw->device_brand_name) && $this->isRealResult($resultRaw->device_brand_name) === true) {
            $device->setBrand($resultRaw->device_brand_name);
        }

        if (isset($resultRaw->device_type) && $this->isRealResult($resultRaw->device_type) === true) {
            // @todo convert to a common set of types (over all vendors)
            $device->setType($resultRaw->device_type);
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
