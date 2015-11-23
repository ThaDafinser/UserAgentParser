<?php
namespace UserAgentParser\Provider;

use UAParser\Parser;
use UserAgentParser\Exception;
use UserAgentParser\Model;

class UAParser extends AbstractProvider
{
    protected $defaultValues = [
        'Other',
    ];

    private $parser;

    public function getName()
    {
        return 'UAParser';
    }

    public function getComposerPackageName()
    {
        return 'ua-parser/uap-php';
    }

    /**
     *
     * @param Parser $parser
     */
    public function setParser(Parser $parser = null)
    {
        $this->parser = $parser;
    }

    /**
     *
     * @return Parser
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $this->parser = Parser::create();

        return $this->parser;
    }

    /**
     *
     * @param \UAParser\Result\Client $resultRaw
     *
     * @return bool
     */
    private function hasResult(\UAParser\Result\Client $resultRaw)
    {
        if ($this->isRealResult($resultRaw->ua->family)) {
            return true;
        }

        if ($this->isRealResult($resultRaw->os->family)) {
            return true;
        }

        if ($this->isRealResult($resultRaw->device->model)) {
            return true;
        }

        return false;
    }

    private function getDeviceModelDefaultValues()
    {
        return [
            'Feature Phone',
            'iOS-Device',
            'Smartphone',
        ];
    }

    private function getDeviceBrandDefaultValues()
    {
        return [
            'Generic',
            'Generic_Android',
            'Generic_Inettv',
        ];
    }

    /**
     *
     * @param \UAParser\Result\Client $resultRaw
     *
     * @return bool
     */
    private function isBot(\UAParser\Result\Client $resultRaw)
    {
        if ($resultRaw->device->family === 'Spider') {
            return true;
        }

        return false;
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();

        /* @var $resultRaw \UAParser\Result\Client */
        $resultRaw = $parser->parse($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($resultRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultRaw);

        /*
         * Bot detection
         */
        if ($this->isBot($resultRaw) === true) {
            $bot = $result->getBot();
            $bot->setIsBot(true);

            if ($this->isRealResult($resultRaw->ua->family) === true) {
                $bot->setName($resultRaw->ua->family);
            }

            return $result;
        }

        /*
         * Browser
         */
        $browser = $result->getBrowser();

        if ($this->isRealResult($resultRaw->ua->family) === true) {
            $browser->setName($resultRaw->ua->family);
        }

        if ($this->isRealResult($resultRaw->ua->major) === true) {
            $browser->getVersion()->setMajor($resultRaw->ua->major);
        }

        if ($this->isRealResult($resultRaw->ua->minor) === true) {
            $browser->getVersion()->setMinor($resultRaw->ua->minor);
        }

        if ($this->isRealResult($resultRaw->ua->patch) === true) {
            $browser->getVersion()->setPatch($resultRaw->ua->patch);
        }

        /*
         * renderingEngine - is currently not possible!
         */

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        if ($this->isRealResult($resultRaw->os->family) === true) {
            $operatingSystem->setName($resultRaw->os->family);
        }

        if ($this->isRealResult($resultRaw->os->major) === true) {
            $operatingSystem->getVersion()->setMajor($resultRaw->os->major);
        }

        if ($this->isRealResult($resultRaw->os->minor) === true) {
            $operatingSystem->getVersion()->setMinor($resultRaw->os->minor);
        }

        if ($this->isRealResult($resultRaw->os->patch) === true) {
            $operatingSystem->getVersion()->setPatch($resultRaw->os->patch);
        }

        /*
         * device
         */
        $device = $result->getDevice();

        if ($this->isRealResult($resultRaw->device->model, $this->getDeviceModelDefaultValues()) === true) {
            $device->setModel($resultRaw->device->model);
        }

        if ($this->isRealResult($resultRaw->device->brand, $this->getDeviceBrandDefaultValues()) === true) {
            $device->setBrand($resultRaw->device->brand);
        }

        return $result;
    }
}
