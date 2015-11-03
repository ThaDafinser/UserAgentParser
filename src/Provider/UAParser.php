<?php
namespace UserAgentParser\Provider;

use UAParser\Parser;

class UAParser extends AbstractProvider
{

    public function getName()
    {
        return 'UAParser';
    }

    public function parse($userAgent)
    {
        $parser = Parser::create();
        
        /* @var $result \UAParser\Result\Client */
        $result = $parser->parse($userAgent);
        
        $raw = [
            'browser' => $result->ua,
            'operatingSystem' => $result->os,
            'device' => $result->device
        ];
        
        /*
         * Browser
         */
        $browserFamily = $result->ua->family;
        if ($browserFamily == 'Other') {
            $browserFamily = null;
        }
        
        $browserVersion = null;
        if ($result->ua->major != '') {
            $browserVersion = $result->ua->major;
            
            if ($result->ua->minor != '') {
                $browserVersion .= '.' . $result->ua->minor;
                
                if ($result->ua->patch != '') {
                    $browserVersion .= '.' . $result->ua->patch;
                }
            }
        }
        
        $osFamily = $result->os->family;
        if ($osFamily == 'Other') {
            $osFamily = null;
        }
        
        $osVersion = null;
        if ($result->os->major != '') {
            $osVersion = $result->os->major;
            
            if ($result->os->minor != '') {
                $osVersion .= '.' . $result->os->minor;
                
                if ($result->os->patch != '') {
                    $osVersion .= '.' . $result->os->patch;
                    
                    if ($result->os->patchMinor != '') {
                        $browserVersion .= '.' . $result->os->patchMinor;
                    }
                }
            }
        }
        
        if($result->device->family == 'Spider'){
            return $this->returnResult([
            
                'browser' => [
                    'family' => $browserFamily,
                    'version' => $browserVersion,
                ],
                
                'bot' => [
                    'isBot' => true,
                
                    'name' => $browserFamily,
                    'type' => null
                ],
                
                'raw' => $raw
            ]);
        }
        
        // device -> family is not useable currently i think...
        // because it contains often the device model
        
        return $this->returnResult([
            
            'browser' => [
                'family' => $browserFamily,
                'version' => $browserVersion,
            ],
            
            'operatingSystem' => [
                'family' => $osFamily,
                'version' => $osVersion,
                'platform' => null
            ],
            
            'device' => [
                'brand' => $result->device->brand,
                'model' => $result->device->model,
                'type' => null,
                
                'isMobile' => null,
            ],
            
            'raw' => $raw
        ]);
    }
}
