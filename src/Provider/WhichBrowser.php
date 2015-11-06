<?php
namespace UserAgentParser\Provider;

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
        if (isset($raw['browser']['alias'])) {
            $browserFamily = $raw['browser']['alias'];
        }

        $browserVersion = null;
        if (isset($raw['browser']['version'])) {
            if(is_array($raw['browser']['version'])) {
                if(isset($raw['browser']['version']['alias'])){
                    $browserVersion = $raw['browser']['version']['alias'];
                } else {
                    $browserVersion = $raw['browser']['version']['value'];
                }

                if(isset($raw['browser']['version']['nickname'])){
                    $osVersion .= ' ' . $raw['browser']['version']['nickname'];
                }
            }
            else {
                $browserVersion = $raw['browser']['version'];
            }
        }
        
        $osFamily = null;
        if (isset($raw['os']['name'])) {
            $osFamily = $raw['os']['name'];
        }
        if (isset($raw['os']['alias'])) {
            $osFamily = $raw['os']['alias'];
        }
        
        $osVersion = null;
        if(isset($raw['os']['version'])){
            if(is_array($raw['os']['version'])) {
                if(isset($raw['os']['version']['alias'])){
                    $osVersion = $raw['os']['version']['alias'];
                } else {
                    $osVersion = $raw['os']['version']['value'];
                }

                if(isset($raw['os']['version']['nickname'])){
                    $osVersion .= ' ' . $raw['os']['version']['nickname'];
                }
            }
            else {
                $osVersion = $raw['os']['version'];
            }
        }

        $deviceManufacturer = null;
        if (isset($raw['device']['manufacturer'])) {
            $deviceManufacturer = $raw['device']['manufacturer'];
        }

        $deviceModel = null;
        if (isset($raw['device']['model'])) {
            $deviceModel = $raw['device']['model'];

            if (isset($raw['device']['series'])) {
                $deviceModel .= ' ' . $raw['device']['series'];
            }
        }
        elseif (isset($raw['device']['series'])) {
            $deviceModel = $raw['device']['series'];
        }

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

            'device' => [
                'brand' => $deviceManufacturer,
                'model' => $deviceModel,
                'type' => $raw['device']['type'],

                'isMobile' => $raw['device']['type'] == 'mobile',
            ],

            'bot' => [
                'isBot' => false,

                'name' => null,
                'type' => null
            ],
            
            'raw' => $raw
        ]);
    }
}
