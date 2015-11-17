<?php
namespace UserAgentParser\Provider;

use Sinergi\BrowserDetector;
use UserAgentParser\Exception;
use UserAgentParser\Model;

class SinergiBrowserDetector extends AbstractProvider
{
    /**
     * Used for unitTests mocking
     *
     * @var BrowserDetector\Browser
     */
    private $browserParser;

    /**
     * Used for unitTests mocking
     *
     * @var BrowserDetector\Os
     */
    private $osParser;

    /**
     * Used for unitTests mocking
     *
     * @var BrowserDetector\Device
     */
    private $deviceParser;

    public function getName()
    {
        return 'SinergiBrowserDetector';
    }

    public function getComposerPackageName()
    {
        return 'sinergi/browser-detector';
    }

    /**
     *
     * @param  string                  $userAgent
     * @return BrowserDetector\Browser
     */
    public function getBrowserParser($userAgent)
    {
        if ($this->browserParser !== null) {
            return $this->browserParser;
        }

        return new BrowserDetector\Browser($userAgent);
    }

    /**
     *
     * @param  string             $userAgent
     * @return BrowserDetector\Os
     */
    public function getOperatingSystemParser($userAgent)
    {
        if ($this->osParser !== null) {
            return $this->osParser;
        }

        return new BrowserDetector\Os($userAgent);
    }

    /**
     *
     * @param  string                 $userAgent
     * @return BrowserDetector\Device
     */
    public function getDeviceParser($userAgent)
    {
        if ($this->deviceParser !== null) {
            return $this->deviceParser;
        }

        return new BrowserDetector\Device($userAgent);
    }

    /**
     *
     * @param BrowserDetector\Browser $browserRaw
     * @param BrowserDetector\Os      $osRaw
     * @param BrowserDetector\Device  $deviceRaw
     *
     * @return boolean
     */
    private function hasResult(BrowserDetector\Browser $browserRaw, BrowserDetector\Os $osRaw, BrowserDetector\Device $deviceRaw)
    {
        if ($this->isRealResult($browserRaw->getName())) {
            return true;
        }

        if ($this->isRealResult($osRaw->getName())) {
            return true;
        }

        if ($this->isRealResult($deviceRaw->getName())) {
            return true;
        }

        if ($browserRaw->isRobot() === true) {
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

        if ($value === BrowserDetector\Browser::UNKNOWN) {
            return false;
        }

        return true;
    }

    public function parse($userAgent, array $headers = [])
    {
        $browserRaw = $this->getBrowserParser($userAgent);
        $osRaw      = $this->getOperatingSystemParser($userAgent);
        $deviceRaw  = $this->getDeviceParser($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($browserRaw, $osRaw, $deviceRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw([
            'browser'         => $browserRaw,
            'operatingSystem' => $osRaw,
            'device'          => $deviceRaw,
        ]);

        /*
         * Bot detection
         */
        if ($browserRaw->isRobot() === true) {
            $bot = $result->getBot();
            $bot->setIsBot(true);

            return $result;
        }

        /*
         * Browser
         */
        $browser = $result->getBrowser();

        if ($this->isRealResult($browserRaw->getName()) === true) {
            $browser->setName($browserRaw->getName());
        }

        if ($this->isRealResult($browserRaw->getVersion()) === true) {
            $browser->getVersion()->setComplete($browserRaw->getVersion());
        }

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        if ($this->isRealResult($osRaw->getName()) === true) {
            $operatingSystem->setName($osRaw->getName());
        }

        if ($this->isRealResult($osRaw->getVersion()) === true) {
            $operatingSystem->getVersion()->setComplete($osRaw->getVersion());
        }

        /*
         * device
         */
        $device = $result->getDevice();

        if ($this->isRealResult($deviceRaw->getName()) === true) {
            $device->setModel($deviceRaw->getName());
        }

        if ($osRaw->isMobile() === true) {
            $device->setIsMobile(true);
        }

        return $result;
    }
}
