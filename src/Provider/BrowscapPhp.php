<?php
namespace UserAgentParser\Provider;

use BrowscapPHP\Browscap;
use stdClass;
use UserAgentParser\Exception;
use UserAgentParser\Model;

class BrowscapPhp extends AbstractProvider
{
    protected $defaultValues = [
        'DefaultProperties',
        'Default Browser',

        'unknown',
    ];

    /**
     *
     * @var Browscap
     */
    private $parser;

    public function __construct(Browscap $parser)
    {
        $this->setParser($parser);
    }

    public function getName()
    {
        return 'BrowscapPhp';
    }

    public function getComposerPackageName()
    {
        return 'browscap/browscap-php';
    }

    public function getVersion()
    {
        return $this->getParser()
            ->getCache()
            ->getVersion();
    }

    /**
     *
     * @param Browscap $parser
     */
    public function setParser(Browscap $parser)
    {
        $this->parser = $parser;
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
     * @return array
     */
    private function getDeviceModelDefaultValues()
    {
        return [
            'general Desktop',
            'general Mobile Device',
            'general Mobile Phone',
            'general Tablet',
        ];
    }

    /**
     *
     * @param  stdClass $resultRaw
     * @return boolean
     */
    private function isBot(stdClass $resultRaw)
    {
        if (! isset($resultRaw->crawler) || $resultRaw->crawler !== true) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param stdClass $resultRaw
     *
     * @return bool
     */
    private function hasResult(stdClass $resultRaw)
    {
        if (! isset($resultRaw->browser)) {
            return false;
        }

        if ($this->isRealResult($resultRaw->browser) !== true) {
            return false;
        }

        return true;
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
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
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
            $bot = $result->getBot();
            $bot->setIsBot(true);

            if (isset($resultRaw->browser) && $this->isRealResult($resultRaw->browser) === true) {
                $bot->setName($resultRaw->browser);
            }

            // @todo convert to a common set of types (over all vendors)
            if (isset($resultRaw->issyndicationreader) && $resultRaw->issyndicationreader === true) {
                $bot->setType('RSS');
            } elseif (isset($resultRaw->browser_type) && $resultRaw->browser_type === 'Bot/Crawler') {
                $bot->setType('Crawler');
            } elseif (isset($resultRaw->browser_type) && $this->isRealResult($resultRaw->browser_type) === true) {
                $bot->setType($resultRaw->browser_type);
            }

            return $result;
        }

        /*
         * browser
         */
        $browser = $result->getBrowser();

        if (isset($resultRaw->browser) && $this->isRealResult($resultRaw->browser) === true) {
            $browser->setName($resultRaw->browser);
        }

        if (isset($resultRaw->version) && $this->isRealResult($resultRaw->version) === true) {
            $browser->getVersion()->setComplete($resultRaw->version);
        }

        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();

        if (isset($resultRaw->renderingengine_name) && $this->isRealResult($resultRaw->renderingengine_name) === true) {
            $renderingEngine->setName($resultRaw->renderingengine_name);
        }

        if (isset($resultRaw->renderingengine_version) && $this->isRealResult($resultRaw->renderingengine_version) === true) {
            $renderingEngine->getVersion()->setComplete($resultRaw->renderingengine_version);
        }

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        if (isset($resultRaw->platform) && $this->isRealResult($resultRaw->platform) === true) {
            $operatingSystem->setName($resultRaw->platform);
        }

        if (isset($resultRaw->platform_version) && $this->isRealResult($resultRaw->platform_version) === true) {
            $operatingSystem->getVersion()->setComplete($resultRaw->platform_version);
        }

        /*
         * device
         */
        $device = $result->getDevice();

        if (isset($resultRaw->device_name) && $this->isRealResult($resultRaw->device_name, $this->getDeviceModelDefaultValues()) === true) {
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

        return $result;
    }
}
