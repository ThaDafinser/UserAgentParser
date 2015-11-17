<?php
namespace UserAgentParser\Provider;

use DeviceDetector\DeviceDetector;
use UserAgentParser\Exception;
use UserAgentParser\Model;

class PiwikDeviceDetector extends AbstractProvider
{
    /**
     *
     * @var DeviceDetector
     */
    private $parser;

    public function getName()
    {
        return 'PiwikDeviceDetector';
    }

    public function getComposerPackageName()
    {
        return 'piwik/device-detector';
    }

    /**
     *
     * @param DeviceDetector $parser
     */
    public function setParser(DeviceDetector $parser = null)
    {
        $this->parser = $parser;
    }

    /**
     *
     * @return DeviceDetector
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $this->parser = new DeviceDetector();

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

            if ($bot['name'] === null || $bot['name'] === DeviceDetector::UNKNOWN) {
                return false;
            }

            return true;
        }

        $client = $dd->getClient();
        if (isset($client['name']) && $this->isRealResult($client['name'])) {
            return true;
        }

        $os = $dd->getOs();
        if (isset($os['name']) && $this->isRealResult($os['name'])) {
            return true;
        }

        if ($dd->getDevice() !== null) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function isRealResult($value)
    {
        if ($value === '' || $value === null) {
            return false;
        }

        if ($value === DeviceDetector::UNKNOWN) {
            return false;
        }

        return true;
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

        $ddClient = $dd->getClient();
        if (isset($ddClient['name']) && $this->isRealResult($ddClient['name']) === true) {
            $browser->setName($ddClient['name']);
        }

        if (isset($ddClient['version']) && $this->isRealResult($ddClient['version']) === true) {
            $browser->getVersion()->setComplete($ddClient['version']);
        }

        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();

        if (isset($ddClient['engine']) && $this->isRealResult($ddClient['engine']) === true) {
            $renderingEngine->setName($ddClient['engine']);
        }

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        $ddOs = $dd->getOs();
        if (isset($ddOs['name']) && $this->isRealResult($ddOs['name']) === true) {
            $operatingSystem->setName($ddOs['name']);
        }

        if (isset($ddOs['version']) && $this->isRealResult($ddOs['version']) === true) {
            $operatingSystem->getVersion()->setComplete($ddOs['version']);
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
