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
     * Initial needed for uniTest mocking
     *
     * @param \UAParser\UAParser $parser
     */
    public function setParser(\UAParser\UAParser $parser)
    {
        $this->parser = $parser;
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

        if ($browserRaw !== null && $this->isRealResult($browserRaw->getFamily()) === true) {
            return true;
        }

        /* @var $osRaw \UAParser\Result\OperatingSystemResult */
        $osRaw = $resultRaw->getOperatingSystem();

        if ($osRaw !== null && $this->isRealResult($osRaw->getFamily()) === true) {
            return true;
        }

        /* @var $deviceRaw \UAParser\Result\DeviceResult */
        $deviceRaw = $resultRaw->getDevice();

        if ($deviceRaw !== null && $this->isRealResult($deviceRaw->getConstructor()) === true) {
            return true;
        }

        if ($deviceRaw !== null && $this->isRealResult($deviceRaw->getModel()) === true) {
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
        if ($value === '' || $value === null) {
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
         * i think this is currently not possible
         */

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

        // removed desktop type, since it's a default value and not really detected
        if ($this->isRealResult($deviceRaw->getType()) === true && $deviceRaw->getType() !== 'desktop') {
            $device->setType($deviceRaw->getType());
        }

        if ($this->isMobile($resultRaw) === true) {
            $device->setIsMobile(true);
        }

        return $result;
    }
}
