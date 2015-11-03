<?php
namespace UserAgentParser\Provider;

use DeviceDetector\DeviceDetector as PiwikDeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract as PiwikDeviceParserAbstract;
use Doctrine\Common\Cache;

class DeviceDetector extends AbstractProvider
{

    private $parser;
    
    public function getName()
    {
        return 'DeviceDetector';
    }

    private function getParser()
    {
        if($this->parser !== null){
            return $this->parser;
        }
    
        PiwikDeviceParserAbstract::setVersionTruncation(PiwikDeviceParserAbstract::VERSION_TRUNCATION_NONE);
        
        $dd = new PiwikDeviceDetector();
        $dd->setCache(new Cache\PhpFileCache('.tmp/'));
        
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
            if(isset($bot['category'])){
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
        if ($browserFamily == PiwikDeviceDetector::UNKNOWN || $browserFamily == '') {
            $browserFamily = null;
        }
        
        $browserVersion = $dd->getClient('version');
        if ($browserVersion == PiwikDeviceDetector::UNKNOWN || $browserVersion == '') {
            $browserVersion = null;
        }
        
        // operatingSystem
        $osName = $dd->getOs('name');
        if ($osName == PiwikDeviceDetector::UNKNOWN || $osName == '') {
            $osName = null;
        }
        
        $osVersion = $dd->getOs('version');
        if ($osVersion == PiwikDeviceDetector::UNKNOWN || $osVersion == '') {
            $osVersion = null;
        }
        
        $osPlatform = $dd->getOs('platform');
        if ($osPlatform == PiwikDeviceDetector::UNKNOWN || $osPlatform == '') {
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
