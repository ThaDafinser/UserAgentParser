<?php
namespace UserAgentParser\Provider;

use phpbrowscap\Browscap;

class PhpBrowscap extends AbstractProvider
{
    private $parser;

    public function getName()
    {
        return 'PhpBrowscap';
    }
    
    private function getParser()
    {
        if($this->parser !== null){
            return $this->parser;
        }
        
        $parser = new Browscap('.tmp');
        $parser->localFile = 'data/php_browscap.ini';
        
        $this->parser = $parser;
        
        return $this->parser;
    }

    public function parse($userAgent)
    {
        $parser = $this->getParser();
        
        $result = $parser->getBrowser($userAgent, true);
        
        $raw = $result;
        unset($raw['browser_name']);
        unset($raw['browser_name_regex']);
        unset($raw['browser_name_pattern']);
        
        /*
         * browser
         */
        $browserFamily = $result['Browser'];
        if ($browserFamily = 'unknown') {
            $browserFamily = null;
        }
        
        /*
         * os
         */
        $osFamily = $result['Platform'];
        if ($osFamily = 'unknown') {
            $osFamily = null;
        }
        
        $osPlatform = null;
        if ($result['Win32'] === true) {
            $osPlatform = 'x86';
        } elseif ($result['Win64'] === true) {
            $osPlatform = 'x64';
        }
        
        /*
         * device
         */
        $deviceType = $result['Device_Type'];
        if ($deviceType == 'unknown') {
            $deviceType = null;
        }
        
        return $this->returnResult([
            
            'browser' => [
                'family' => $browserFamily,
                'version' => $result['Version']
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
                
                'isMobile' => $result['isMobileDevice']
            ],
            
            'raw' => $raw
        ]);
    }
}
