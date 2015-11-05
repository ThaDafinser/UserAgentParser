<?php
namespace UserAgentParser\Provider;

use BrowscapPHP\Browscap;
use BrowscapPHP\Cache\BrowscapCache;
use WurflCache\Adapter\File;

class BrowscapPhp extends AbstractProvider
{
    private $parser;

    public function getName()
    {
        return 'BrowscapPhp';
    }
    
    /**
     * 
     * @return \BrowscapPHP\Browscap
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }
        
        $cacheAdapter = new File(array(
            File::DIR => '.tmp/browscap'
        ));
        $cache = new BrowscapCache($cacheAdapter);
        
        $parser = new Browscap();
        $parser->setCache($cache);
        
        $this->parser = $parser;
        
        return $this->parser;
    }

    public function parse($userAgent)
    {
        $parser = $this->getParser();
        
        /* @var $raw \stdClass */
        $raw = $parser->getBrowser($userAgent);

        unset($raw->browser_name);
        unset($raw->browser_name_regex);
        unset($raw->browser_name_pattern);
        
        
        /*
         * browser
         */
        $browserFamily = $raw->browser;
        if ($browserFamily == 'unknown' || $browserFamily == 'Default Browser') {
            $browserFamily = null;
        }
        
        /*
         * os
         */
        $osFamily = $raw->platform;
        if ($osFamily = 'unknown') {
            $osFamily = null;
        }
        
        $osPlatform = null;
        if ($raw->win32 === true) {
            $osPlatform = 'x86';
        } elseif ($raw->win64 === true) {
            $osPlatform = 'x64';
        }
        
        /*
         * device
         */
        $deviceType = $raw->device_type;
        if ($deviceType == 'unknown') {
            $deviceType = null;
        }
        
        return $this->returnResult([
            
            'browser' => [
                'family' => $browserFamily,
                'version' => $raw->version
            ],
            
            'operatingSystem' => [
                'family' => $osFamily,
                'version' => null,
                'platform' => $osPlatform
            ],
            
            'device' => [
                'brand' => null,
                'model' => null,
                'type' => $deviceType,
                
                'isMobile' => $raw->ismobiledevice
            ],
            
            'raw' => $raw
        ]);
    }
}
