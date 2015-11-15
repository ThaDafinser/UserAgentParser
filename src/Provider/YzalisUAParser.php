<?php
namespace UserAgentParser\Provider;

use UAParser\Result\Result as UAParserResult;
use UserAgentParser\Exception;
use UserAgentParser\Model;

class YzalisUAParser extends AbstractProvider
{
    private $parser;

    public function getName()
    {
        return 'YzalisUAParser';
    }

    public function getComposerPackageName()
    {
        return 'yzalis/ua-parser';
    }

    /**
     *
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
        /* @var $browserRaw \UAParser\Result\BrowserResult */
        $browserRaw = $resultRaw->getBrowser();

        if ($browserRaw->getFamily() !== 'Other') {
            return true;
        }

        /* @var $osRaw \UAParser\Result\OperatingSystemResult */
        $osRaw = $resultRaw->getOperatingSystem();

        if ($osRaw->getFamily() !== 'Other') {
            return true;
        }

        /* @var $deviceRaw \UAParser\Result\DeviceResult */
        $deviceRaw = $resultRaw->getDevice();

        if ($deviceRaw->getConstructor() !== 'Other') {
            return true;
        }

        /* @var $emailRaw \UAParser\Result\EmailClientResult */
        $emailRaw = $resultRaw->getEmailClient();

        if ($emailRaw->getFamily() !== 'Other') {
            return true;
        }

        return false;
    }

    /**
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function isRealResult($value)
    {
        if ($value === '') {
            return false;
        }

        if ($value === 'Other') {
            return false;
        }

        return true;
    }

    /**
     *
     * @param UAParserResult $resultRaw
     *
     * @return bool
     */
    private function isMobile(UAParserResult $resultRaw)
    {
        /* @var $deviceRaw \UAParser\Result\DeviceResult */
        $deviceRaw = $resultRaw->getDevice();

        if ($deviceRaw->getType() === 'mobile') {
            return true;
        }

        if ($deviceRaw->getType() === 'tablet') {
            return true;
        }

        return false;
    }

    public function parse($userAgent, array $headers = [])
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

        /* @var $browserRaw \UAParser\Result\BrowserResult */
        $browserRaw = $resultRaw->getBrowser();

        /* @var $renderingEngineRaw \UAParser\Result\RenderingEngineResult */
        $renderingEngineRaw = $resultRaw->getRenderingEngine();

        /* @var $osRaw \UAParser\Result\OperatingSystemResult */
        $osRaw = $resultRaw->getOperatingSystem();

        /* @var $deviceRaw \UAParser\Result\DeviceResult */
        $deviceRaw = $resultRaw->getDevice();

        /* @var $emailRaw \UAParser\Result\EmailClientResult */
        // currently not used...any idea to implement it?

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultRaw);

        /*
         * Bot detection
         */
        // @todo i think this is currently not possible

        /*
         * Browser
         */
        $browser = $result->getBrowser();

        if ($this->isRealResult($browserRaw->getFamily()) === true) {
            $browser->setName($browserRaw->getFamily());
        }

        if ($this->isRealResult($browserRaw->getVersionString()) === true) {
            $browser->getVersion()->setComplete($browserRaw->getVersionString());
        }

        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();

        if ($this->isRealResult($renderingEngineRaw->getFamily()) === true) {
            $renderingEngine->setName($renderingEngineRaw->getFamily());
        }

        if ($this->isRealResult($renderingEngineRaw->getVersion()) === true) {
            $renderingEngine->getVersion()->setComplete($renderingEngineRaw->getVersion());
        }

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        if ($this->isRealResult($osRaw->getFamily()) === true) {
            $operatingSystem->setName($osRaw->getFamily());
        }

        if ($this->isRealResult($osRaw->getMajor()) === true) {
            $operatingSystem->getVersion()->setMajor($osRaw->getMajor());

            if ($this->isRealResult($osRaw->getMinor()) === true) {
                $operatingSystem->getVersion()->setMinor($osRaw->getMinor());
            }

            if ($this->isRealResult($osRaw->getPatch()) === true) {
                $operatingSystem->getVersion()->setPatch($osRaw->getPatch());
            }
        }

        /*
         * device
         */
        $device = $result->getDevice();

        if ($this->isRealResult($deviceRaw->getModel()) === true) {
            $device->setModel($deviceRaw->getModel());
        }

        if ($this->isRealResult($deviceRaw->getConstructor()) === true) {
            $device->setBrand($deviceRaw->getConstructor());
        }

        if ($this->isRealResult($deviceRaw->getType()) === true) {
            $device->setType($deviceRaw->getType());
        }

        if ($this->isMobile($resultRaw) === true) {
            $device->setIsMobile(true);
        }

        return $result;
    }
}
