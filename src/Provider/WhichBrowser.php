<?php
namespace UserAgentParser\Provider;

include 'vendor/whichbrowser/whichbrowser/libraries/whichbrowser.php';

use UserAgentParser\Exception;
use UserAgentParser\Model;

class WhichBrowser extends AbstractProvider
{

    private $parser;

    public function getName()
    {
        return 'WhichBrowser';
    }

    /**
     *
     * @return \WhichBrowser
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }
        
        $parser = new \WhichBrowser([]);
        
        $this->parser = $parser;
        
        return $this->parser;
    }

    /**
     *
     * @param array $resultRaw            
     * @return boolean
     */
    private function hasResult(array $resultRaw)
    {
        if (count($resultRaw) === 0) {
            return false;
        }
        
        return true;
    }

    /**
     *
     * @param mixed $value            
     * @return boolean
     */
    private function isRealResult($value)
    {
        return true;
    }

    private function isBot(array $resultRaw)
    {
        if (isset($resultRaw['device']['type']) && $resultRaw['device']['type'] === 'bot') {
            return true;
        }
        
        return false;
    }

    /**
     *
     * @param array $resultRaw            
     * @return boolean
     */
    private function isMobile(array $resultRaw)
    {
        if (! isset($resultRaw['device']['type'])) {
            return false;
        }
        
        /*
         * Available types...
         *
         * define ('TYPE_DESKTOP', 'desktop');
         * define ('TYPE_MOBILE', 'mobile'); <--
         * define ('TYPE_DECT', 'dect');
         * define ('TYPE_TABLET', 'tablet'); <--
         * define ('TYPE_GAMING', 'gaming');
         * define ('TYPE_EREADER', 'ereader'); <--
         * define ('TYPE_MEDIA', 'media'); <-- e.g. iPod
         * define ('TYPE_HEADSET', 'headset');
         * define ('TYPE_WATCH', 'watch'); <--
         * define ('TYPE_EMULATOR', 'emulator');
         * define ('TYPE_TELEVISION', 'television');
         * define ('TYPE_MONITOR', 'monitor');
         * define ('TYPE_CAMERA', 'camera'); <--
         * define ('TYPE_SIGNAGE', 'signage');
         * define ('TYPE_WHITEBOARD', 'whiteboard');
         */
        
        if ($resultRaw['device']['type'] === TYPE_MOBILE) {
            return true;
        }
        
        if ($resultRaw['device']['type'] === TYPE_TABLET) {
            return true;
        }
        
        if ($resultRaw['device']['type'] === TYPE_EREADER) {
            return true;
        }
        
        if ($resultRaw['device']['type'] === TYPE_MEDIA) {
            return true;
        }
        
        if ($resultRaw['device']['type'] === TYPE_WATCH) {
            return true;
        }
        
        if ($resultRaw['device']['type'] === TYPE_CAMERA) {
            return true;
        }
        
        return false;
    }

    /**
     *
     * @param array $resultRaw            
     * @return boolean
     */
    private function isTouch(array $resultRaw)
    {
        if (! isset($resultRaw['device']['type'])) {
            return false;
        }
        if (isset($raw['os']['alias'])) {
            $osFamily = $raw['os']['alias'];
        }
        
        // @todo i'm not sure currently..e.g feature phone?
        // if ($resultRaw['device']['type'] === TYPE_MOBILE) {
        // return true;
        // }
        
        if ($resultRaw['device']['type'] === TYPE_TABLET) {
            return true;
        }
        
        if ($resultRaw['device']['type'] === TYPE_EREADER) {
            return true;
        }
        
        return false;
    }

    public function parse($userAgent)
    {
        $parser = $this->getParser();
        $parser->analyseUserAgent($userAgent);
        
        $resultRaw = $parser->toArray();
        
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
         * Bot detection
         */
        if ($this->isBot($resultRaw) === true) {
            $bot = $result->getBot();
            $bot->setIsBot(true);
            
            if (isset($resultRaw['browser']['name']) && $this->isRealResult($resultRaw['browser']['name']) === true) {
                $bot->setName($resultRaw['browser']['name']);
            }
            
            return $result;
        }
        
        /*
         * Browser
         */
        $browser = $result->getBrowser();
        
        if (isset($resultRaw['browser']['name']) && $this->isRealResult($resultRaw['browser']['name']) === true) {
            $browser->setName($resultRaw['browser']['name']);
        }
        
        if (isset($resultRaw['browser']['version']) && $this->isRealResult($resultRaw['browser']['version']) === true) {
            $browser->getVersion()->setComplete($resultRaw['browser']['version']);
        }
        
        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();
        
        if (isset($resultRaw['engine']['name']) && $this->isRealResult($resultRaw['engine']['name']) === true) {
            $renderingEngine->setName($resultRaw['engine']['name']);
        }
        
        if (isset($resultRaw['engine']['version']) && $this->isRealResult($resultRaw['engine']['version']) === true) {
            $renderingEngine->getVersion()->setComplete($resultRaw['engine']['version']);
        }
        
        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();
        
        if (isset($resultRaw['os']['name']) && $this->isRealResult($resultRaw['os']['name']) === true) {
            $operatingSystem->setName($resultRaw['os']['name']);
        }
        
        if (isset($resultRaw['os']['version']) && $this->isRealResult($resultRaw['os']['version']) === true) {
            $operatingSystem->getVersion()->setComplete($resultRaw['os']['version']);
        }
        
        /*
         * device
         */
        $device = $result->getDevice();
        
        if (isset($resultRaw['device']['model']) && $this->isRealResult($resultRaw['device']['model']) === true) {
            $device->setModel($resultRaw['device']['model']);
        }
        
        if (isset($resultRaw['device']['manufacturer']) && $this->isRealResult($resultRaw['device']['manufacturer']) === true) {
            $device->setBrand($resultRaw['device']['manufacturer']);
        }
        
        if (isset($resultRaw['device']['type']) && $this->isRealResult($resultRaw['device']['type']) === true) {
            $device->setType($resultRaw['device']['type']);
        }
        
        if ($this->isMobile($resultRaw) === true) {
            $device->setIsMobile(true);
        }
        
        if ($this->isTouch($resultRaw) === true) {
            $device->setIsTouch(true);
        }
        
        return $result;
    }
}
