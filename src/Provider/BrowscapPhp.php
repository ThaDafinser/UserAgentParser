<?php
namespace UserAgentParser\Provider;

use BrowscapPHP\Browscap;
use BrowscapPHP\Cache\BrowscapCache;
use stdClass;
use UserAgentParser\Exception;
use UserAgentParser\Model;
use WurflCache\Adapter\File;

class BrowscapPhp extends AbstractProvider
{
    private $parser;

    private $cachePath = '.tmp/browscap_lite';

    public function getName()
    {
        return 'BrowscapPhp';
    }

    /**
     * @param string $path
     */
    public function setCachePath($path)
    {
        $this->cachePath = $path;
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * @return \BrowscapPHP\Browscap
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $cacheAdapter = new File([
            File::DIR => $this->getCachePath(),
        ]);
        $cache = new BrowscapCache($cacheAdapter);

        $parser = new Browscap();
        $parser->setCache($cache);

        $this->parser = $parser;

        return $this->parser;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function isRealResult($value)
    {
        if ($value === '') {
            return false;
        }

        if ($value === 'unknown') {
            return false;
        }

        if ($value === 'Default Browser') {
            return false;
        }

        return true;
    }

    private function isBot(stdClass $resultRaw)
    {
        if (!isset($resultRaw->crawler)) {
            return false;
        }

        if ($resultRaw->crawler === true) {
            return true;
        }

        // if ($resultRaw->browser_type === 'Application') {
        // return true;
        // }

        // if ($resultRaw->browser_type === 'Bot/Crawler') {
        // return true;
        // }

        return false;
    }

    /**
     * @param stdClass $resultRaw
     *
     * @return bool
     */
    private function hasResult(stdClass $resultRaw)
    {
        if (!isset($resultRaw->browser)) {
            return false;
        }

        if ($this->isRealResult($resultRaw->browser) !== true) {
            return false;
        }

        return true;
    }

    public function parse($userAgent)
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

            if (isset($resultRaw->version) && $this->isRealResult($resultRaw->version) === true) {
                $bot->setName($resultRaw->browser);
            }

            $rssString = '';
            if ($resultRaw->issyndicationreader === true) {
                $rssString .= ' (is RSS)';
            }

            // @todo convert to a common set of types (over all vendors)
            // @note $resultRaw->issyndicationreader is also useable for the detection
            $bot->setType($resultRaw->browser_type . $rssString);

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
            // do not apply empty version strings
            $left = preg_replace('/[0.]/', '', $resultRaw->version);
            if ($left !== '') {
                $browser->getVersion()->setComplete($resultRaw->version);
            }
        }

        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();

        if (isset($resultRaw->renderingengine_name) && $this->isRealResult($resultRaw->renderingengine_name) === true) {
            $renderingEngine->setName($resultRaw->renderingengine_name);
        }

        if (isset($resultRaw->renderingengine_version) && $this->isRealResult($resultRaw->renderingengine_version) === true) {
            $renderingEngine->setName($resultRaw->renderingengine_version);
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

        if (isset($resultRaw->device_name) && $this->isRealResult($resultRaw->device_name) === true) {
            $device->setModel($resultRaw->device_name);
        }

        if (isset($resultRaw->device_brand_name) && $this->isRealResult($resultRaw->device_brand_name) === true) {
            $device->setBrand($resultRaw->device_brand_name);
        }

        if (isset($resultRaw->device_type) && $this->isRealResult($resultRaw->device_type) === true) {
            // @todo convert to a common set of types (over all vendors)
            $device->setType($resultRaw->device_type);
        }

        if (isset($resultRaw->ismobiledevice) && $this->isRealResult($resultRaw->ismobiledevice) === true) {
            $device->setIsMobile($resultRaw->ismobiledevice);
        }

        if (isset($resultRaw->device_pointing_method) && $resultRaw->device_pointing_method == 'touchscreen') {
            $device->setIsTouch(true);
        }

        return $result;
    }
}
