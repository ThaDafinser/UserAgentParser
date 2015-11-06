<?php
namespace UserAgentParser\Provider;

// include 'vendor/whichbrowser/whichbrowser/libraries/whichbrowser.php';
include 'vendor/whichbrowser/whichbrowser/libraries/whichbrowser.php';

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
    public function parse($userAgent)
    {
        $parser = new \WhichBrowser(array('headers' => array('User-Agent' => $userAgent)));
        $raw = $parser->toArray();
        
        /*
         * no result found
         */
        if (count($raw) === 0) {
            return $this->returnResult([
                'raw' => $raw
            ]);
        }
        
        $browserFamily = null;
        if (isset($raw['browser']['name'])) {
            $browserFamily = $raw['browser']['name'];
        }
        
        $browserVersion = null;
        if (isset($raw['browser']['version'])) {
            $browserVersion = $raw['browser']['version'];
        }
        
        $osFamily = null;
        if (isset($raw['os']['name'])) {
            $osFamily = $raw['os']['name'];
        }
        
        $osVersion = null;
        // if(isset($raw['os']['version'])){
        // $osVersion = $raw['os']['version'];
        // }

        /*
         * Bot found
         */
        if (isset($raw['device']['type']) && $raw['device']['type'] == 'bot') {
            return $this->returnResult([
                'browser' => [
                    'family' => $browserFamily,
                    'version' => null
                ],
                
                'bot' => [
                    'isBot' => true,
                    
                    'name' => $browserFamily,
                    'type' => null
                ],
                
                'raw' => $raw
            ]);
        }
        
        return $this->returnResult([
            'browser' => [
                'family' => $browserFamily,
                'version' => $browserVersion
            ],
            
            'operatingSystem' => [
                'family' => $osFamily,
                'version' => $osVersion,
                'platform' => null
            ],
            
            'raw' => $raw
        ]);
    }
}
