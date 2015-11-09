<?php
namespace UserAgentParser\Provider;

use UAParser\Result\Result as UAParserResult;

class YzalisUAParser extends AbstractProvider
{
    private $parser;

    public function getName()
    {
        return 'YzalisUAParser';
    }

    /**
     * @return \UAParser\UAParser
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $uaParser = new \UAParser\UAParser();

        $this->parser = $uaParser;

        return $this->parser;
    }
    
    /**
     *
     * @param UAParserResult $resultRaw
     *
     * @return bool
     */
    private function hasResult(UAParserResult $resultRaw)
    {
        if($resultRaw->browser){
            
        }
        
        return true;
    }

    public function parse($userAgent)
    {
        $parser = $this->getParser();

        /* @var $resultRaw \UAParser\Result\Result */
        $resultRaw = $parser->parse($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($resultRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }
        
        var_dump($resultRaw);
        exit();
        
        /* @var $browser \UAParser\Result\BrowserResult */
        $browser = $result->getBrowser();

        $browserFamily  = null;
        $browserVersion = null;
        if ($browser->getFamily() != 'Other') {
            $browserFamily = $browser->getFamily();

            if ($browser->getVersionString() != 'Other') {
                $browserVersion = $browser->getVersionString();
            }
        }

        /* @var $os \UAParser\Result\OperatingSystemResult */
        $os = $result->getOperatingSystem();

        $osName    = null;
        $osVersion = null;
        if ($os->getFamily() != 'Other') {
            $osName = $os->getFamily();

            if ($os->getMajor() != '') {
                $osVersion = $os->getMajor();
            }
        }

        /* @var $device \UAParser\Result\DeviceResult */
        $device = $result->getDevice();

        $deviceBrand = null;
        if ($device->getConstructor() != 'Other' && $device->getConstructor() != '') {
            $deviceBrand = $device->getConstructor();
        }

        $deviceModel = null;
        if ($device->getModel() != 'Other' && $device->getModel() != '') {
            $deviceModel = $device->getModel();
        }

        $deviceType = null;
        if ($device->getType() != 'desktop' && $device->getType() != '') {
            $deviceType = $device->getType();
        }

        /* @var $email \UAParser\Result\EmailClientResult */
        $email = $result->getEmailClient();
        if ($email->getFamily() != 'Other' && $browser->getFamily() == 'Other') {
            $browserFamily = $email->getFamily();
            if ($email->getMajor() != 'Other') {
                $browserVersion = $email->getMajor();
            }
        }

        return $this->returnResult([

            'browser' => [
                'family'  => $browserFamily,
                'version' => $browserVersion,
            ],

            'device' => [
                'brand' => $deviceBrand,
                'model' => $deviceModel,
                'type'  => $deviceType,

                'isMobile' => null,
            ],

            'operatingSystem' => [
                'family'   => $osName,
                'version'  => $osVersion,
                'platform' => null,
            ],

            'raw' => $result,
        ]);
    }
}
