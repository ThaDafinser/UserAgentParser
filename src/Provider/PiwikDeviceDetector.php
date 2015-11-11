<?php
namespace UserAgentParser\Provider;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use Doctrine\Common\Cache;
use Doctrine\Common\Cache\CacheProvider;
use UserAgentParser\Exception;
use UserAgentParser\Model;

class PiwikDeviceDetector extends AbstractProvider
{
    /**
     *
     * @var CacheProvider
     */
    private $cache;

    /**
     *
     * @var DeviceDetector
     */
    private $parser;

    public function getName()
    {
        return 'PiwikDeviceDetector';
    }

    /**
     *
     * @param CacheProvider $cache
     */
    public function setCache(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     *
     * @return CacheProvider
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     *
     * @return DeviceDetector
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);

        $dd = new DeviceDetector();
        if ($this->getCache() !== null) {
            $dd->setCache($this->getCache());
        }

        $this->parser = $dd;

        return $this->parser;
    }

    /**
     *
     * @param DeviceDetector $dd
     *
     * @return bool
     */
    private function hasResult(DeviceDetector $dd)
    {
        if ($dd->isBot() === true) {
            $bot = $dd->getBot();

            if ($bot['name'] === DeviceDetector::UNKNOWN) {
                return false;
            }

            return true;
        }

        if ($dd->getClient('name') !== DeviceDetector::UNKNOWN) {
            return true;
        }

        if ($dd->getOs('name') !== DeviceDetector::UNKNOWN) {
            return true;
        }

        if ($dd->getDevice() !== null) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param DeviceDetector $dd
     *
     * @return array
     */
    private function getResultRaw(DeviceDetector $dd)
    {
        $raw = [
            'client'          => $dd->getClient(),
            'operatingSystem' => $dd->getOs(),

            'device' => [
                'brand'     => $dd->getBrand(),
                'brandName' => $dd->getBrandName(),

                'model' => $dd->getModel(),

                'device'     => $dd->getDevice(),
                'deviceName' => $dd->getDeviceName(),
            ],

            'bot' => $dd->getBot(),

            'extra' => [
                'isBot' => $dd->isBot(),

                // client
                'isBrowser'     => $dd->isBrowser(),
                'isFeedReader'  => $dd->isFeedReader(),
                'isMobileApp'   => $dd->isMobileApp(),
                'isPIM'         => $dd->isPIM(),
                'isLibrary'     => $dd->isLibrary(),
                'isMediaPlayer' => $dd->isMediaPlayer(),

                // deviceType
                'isCamera'              => $dd->isCamera(),
                'isCarBrowser'          => $dd->isCarBrowser(),
                'isConsole'             => $dd->isConsole(),
                'isFeaturePhone'        => $dd->isFeaturePhone(),
                'isPhablet'             => $dd->isPhablet(),
                'isPortableMediaPlayer' => $dd->isPortableMediaPlayer(),
                'isSmartDisplay'        => $dd->isSmartDisplay(),
                'isSmartphone'          => $dd->isSmartphone(),
                'isTablet'              => $dd->isTablet(),
                'isTV'                  => $dd->isTV(),

                // other special
                'isDesktop'      => $dd->isDesktop(),
                'isMobile'       => $dd->isMobile(),
                'isTouchEnabled' => $dd->isTouchEnabled(),
            ],
        ];

        return $raw;
    }

    /**
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function isRealResult($value)
    {
        if ($value === '') {
            return false;
        }

        if ($value === DeviceDetector::UNKNOWN) {
            return false;
        }

        return true;
    }

    public function parse($userAgent, array $headers = [])
    {
        $dd = $this->getParser();

        $dd->setUserAgent($userAgent);
        $dd->parse();

        /*
         * No result found?
         */
        if ($this->hasResult($dd) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($this->getResultRaw($dd));

        /*
         * Bot detection
         */
        if ($dd->isBot() === true) {
            $bot = $result->getBot();
            $bot->setIsBot(true);

            $botRaw = $dd->getBot();
            if (isset($botRaw['name']) && $this->isRealResult($botRaw['name'])) {
                $bot->setName($botRaw['name']);
            }
            if (isset($botRaw['category']) && $this->isRealResult($botRaw['category'])) {
                $bot->setType($botRaw['category']);
            }

            return $result;
        }

        /*
         * Browser
         */
        $browser = $result->getBrowser();

        if ($this->isRealResult($dd->getClient('name')) === true) {
            $browser->setName($dd->getClient('name'));
        }

        if ($this->isRealResult($dd->getClient('version')) === true) {
            $browser->getVersion()->setComplete($dd->getClient()['version']);
        }

        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();

        if ($this->isRealResult($dd->getClient('engine')) === true) {
            $renderingEngine->setName($dd->getClient('engine'));
        }

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        if ($this->isRealResult($dd->getOs('name')) === true) {
            $operatingSystem->setName($dd->getOs('name'));
        }

        if ($this->isRealResult($dd->getOs('version')) === true) {
            $operatingSystem->getVersion()->setComplete($dd->getOs()['version']);
        }

        /*
         * device
         */
        $device = $result->getDevice();

        if ($this->isRealResult($dd->getModel()) === true) {
            $device->setModel($dd->getModel());
        }

        if ($this->isRealResult($dd->getBrandName()) === true) {
            $device->setBrand($dd->getBrandName());
        }

        if ($this->isRealResult($dd->getDeviceName()) === true) {
            $device->setType($dd->getDeviceName());
        }

        if ($dd->isMobile() === true) {
            $device->setIsMobile(true);
        }

        if ($dd->isTouchEnabled() === true) {
            $device->setIsTouch(true);
        }

        return $result;
    }
}
