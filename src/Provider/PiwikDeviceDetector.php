<?php
namespace UserAgentParser\Provider;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use Doctrine\Common\Cache;

class PiwikDeviceDetector extends AbstractProvider
{
    private $parser;
    
    public function getName()
    {
        return 'PiwikDeviceDetector';
    }

    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }
    
        DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);
        
        $dd = new DeviceDetector();
        $dd->setCache(new Cache\PhpFileCache('.tmp/piwik'));
        
        $this->parser = $dd;
    
        return $this->parser;
    }
    
    public function parse($userAgent)
    {
        $dd = $this->getParser();
        
        $dd->setUserAgent($userAgent);
        $dd->parse();
        
        $raw = [
            'client' => $dd->getClient(),
            'operatingSystem' => $dd->getOs(),
            
            'device' => [
                'brand' => $dd->getBrand(),
                'brandName' => $dd->getBrandName(),
                
                'model' => $dd->getModel(),
                
                'device' => $dd->getDevice(),
                'deviceName' => $dd->getDeviceName()
            ],
            
            'bot' => $dd->getBot(),
            
            'extra' => [
                'isBot' => $dd->isBot(),
                
                // client
                'isBrowser' => $dd->isBrowser(),
                'isFeedReader' => $dd->isFeedReader(),
                'isMobileApp' => $dd->isMobileApp(),
                'isPIM' => $dd->isPIM(),
                'isLibrary' => $dd->isLibrary(),
                'isMediaPlayer' => $dd->isMediaPlayer(),
                
                // deviceType
                'isCamera' => $dd->isCamera(),
                'isCarBrowser' => $dd->isCarBrowser(),
                'isConsole' => $dd->isConsole(),
                'isFeaturePhone' => $dd->isFeaturePhone(),
                'isPhablet' => $dd->isPhablet(),
                'isPortableMediaPlayer' => $dd->isPortableMediaPlayer(),
                'isSmartDisplay' => $dd->isSmartDisplay(),
                'isSmartphone' => $dd->isSmartphone(),
                'isTablet' => $dd->isTablet(),
                'isTV' => $dd->isTV(),
                
                // other special
                'isDesktop' => $dd->isDesktop(),
                'isMobile' => $dd->isMobile(),
                'isTouchEnabled' => $dd->isTouchEnabled()
            ]
        ];
        
        if ($dd->isBot() === true) {
            $bot = $dd->getBot();
            
            $category = null;
            if (isset($bot['category'])) {
                $category = $bot['category'];
            }
            
            return $this->returnResult([
                
                'browser' => [
                    'family' => $bot['name'],
                    'version' => null,
                ],
                
                'bot' => [
                    'isBot' => true,
                    
                    'name' => $bot['name'],
                    'type' => $category
                ],
                
                'raw' => $raw
            ]);
        }
        
        // browser
        $browserFamily = $dd->getClient('name');
        if ($browserFamily == DeviceDetector::UNKNOWN || $browserFamily == '') {
            $browserFamily = null;
        }
        
        $browserVersion = $dd->getClient('version');
        if ($browserVersion == DeviceDetector::UNKNOWN || $browserVersion == '') {
            $browserVersion = null;
        }
        
        // operatingSystem
        $osName = $dd->getOs('name');
        if ($osName == DeviceDetector::UNKNOWN || $osName == '') {
            $osName = null;
        }
        
        $osVersion = $dd->getOs('version');
        if ($osVersion == DeviceDetector::UNKNOWN || $osVersion == '') {
            $osVersion = null;
        }
        
        $osPlatform = $dd->getOs('platform');
        if ($osPlatform == DeviceDetector::UNKNOWN || $osPlatform == '') {
            $osPlatform = null;
        }
        
        $deviceType = $dd->getDeviceName();
        if ($deviceType == '') {
            $deviceType = null;
        }
        
        return $this->returnResult([
            
            'browser' => [
                'family' => $browserFamily,
                'version' => $browserVersion
            ],
            
            'operatingSystem' => [
                'family' => $osName,
                'version' => $osVersion,
                'platform' => $osPlatform
            ],
            
            'device' => [
                'brand' => $dd->getBrandName(),
                'model' => $dd->getModel(),
                'type' => $deviceType,
                
                'isMobile' => $dd->isMobile()
            ],
            
            'raw' => $raw
        ]);
        
        return $dd;
    }
}
