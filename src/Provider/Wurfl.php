<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;
use Wurfl\CustomDevice;

class Wurfl extends AbstractProvider
{
    public function getName()
    {
        return 'Wurfl';
    }

    public function getComposerPackageName()
    {
        return 'mimmi20/wurfl';
    }

    public function getVersion()
    {
        return $this->getManager()->getWurflInfo()->version;
    }

    /**
     *
     * @return \Wurfl\Configuration\InMemoryConfig
     */
    private function getConfig()
    {
        $resourcesDir = '.tmp/wurfl';

        $persistenceDir = $resourcesDir . '/storage/persistence';
        $cacheDir       = $resourcesDir . '/storage/cache';

        // Create WURFL Configuration
        $wurflConfig = new \Wurfl\Configuration\InMemoryConfig();

        // Set location of the WURFL File
        $wurflConfig->wurflFile('.tmp/wurfl.xml');

        /*
         * Set the match mode for the API
         *
         * It is recommended to use the defined class constants instead of their
         * string values:
         *
         * \Wurfl\Configuration\Config::MATCH_MODE_PERFORMANCE
         * \Wurfl\Configuration\Config::MATCH_MODE_ACCURACY
         */
        $wurflConfig->matchMode(\Wurfl\Configuration\Config::MATCH_MODE_ACCURACY);

        // Setup WURFL Persistence
        $wurflConfig->persistence('file', [
            \Wurfl\Configuration\Config::DIR => $persistenceDir,
        ]);

        // Setup Caching
        $wurflConfig->cache('file', [
            \Wurfl\Configuration\Config::DIR        => $cacheDir,
            \Wurfl\Configuration\Config::EXPIRATION => 36000,
        ]);

        return $wurflConfig;
    }

    /**
     *
     * @return \Wurfl\Manager
     */
    private function getManager()
    {
        $wurflConfig = $this->getConfig();

        // Create the cache instance from the configuration
        $cacheStorage = \Wurfl\Storage\Factory::create($wurflConfig->cache);

        // Create the persistent cache instance from the configuration
        $persistenceStorage = \Wurfl\Storage\Factory::create($wurflConfig->persistence);

        // Create a WURFL Manager from the WURFL Configuration
        $wurflManager = new \Wurfl\Manager($wurflConfig, $persistenceStorage, $cacheStorage);

        return $wurflManager;
    }

    /**
     *
     * @param  CustomDevice $device
     * @return boolean
     */
    private function hasResult(CustomDevice $device)
    {
        if ($device->id !== 'generic') {
            return true;
        }

        return false;
    }

    public function parse($userAgent, array $headers = [])
    {
        $manager = $this->getManager();

        $deviceRaw = $manager->getDeviceForUserAgent($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($deviceRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($deviceRaw->getAllVirtualCapabilities());

        var_dump($deviceRaw->getAllVirtualCapabilities());
        var_dump($deviceRaw->getAllCapabilities());
        exit();

        /*
         * Bot detection
         */
        if ($deviceRaw->getVirtualCapability('is_robot') === 'true') {
            $bot = $result->getBot();
            $bot->setIsBot(true);

            // brand_name seems to be always google, so dont use it

            return $result;
        }

        /*
         * browser
         */
        $browser = $result->getBrowser();

        $browser->setName($deviceRaw->getVirtualCapability('advertised_browser'));
        $browser->getVersion()->setComplete($deviceRaw->getVirtualCapability('advertised_browser_version'));

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        $operatingSystem->setName($deviceRaw->getVirtualCapability('advertised_device_os'));
        $operatingSystem->getVersion()->setComplete($deviceRaw->getVirtualCapability('advertised_device_os_version'));

        /*
         * device
         */
        $device = $result->getDevice();

        if ($deviceRaw->getVirtualCapability('is_full_desktop') === 'false') {
            $device->setModel($deviceRaw->getCapability('model_name'));
            $device->setBrand($deviceRaw->getCapability('brand_name'));

            if ($deviceRaw->getVirtualCapability('is_mobile') === 'true') {
                $device->setIsMobile(true);
            }

            if ($deviceRaw->getVirtualCapability('is_touchscreen') === 'true') {
                $device->setIsTouch(true);
            }
        }

        return $result;
    }
}
