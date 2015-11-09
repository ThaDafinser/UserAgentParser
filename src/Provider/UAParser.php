<?php
namespace UserAgentParser\Provider;

use UAParser\Parser;
use UserAgentParser\Exception;
use UserAgentParser\Model;

class UAParser extends AbstractProvider
{
    private $parser;

    public function getName()
    {
        return 'UAParser';
    }

    /**
     * @return Parser
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $this->parser = Parser::create();

        return $this->parser;
    }

    /**
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

        if ($this->isRealResult($resultRaw->device->family)) {
            return true;
        }

        return false;
    }

    /**
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

    public function parse($userAgent)
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

        if ($this->isRealResult($resultRaw->device->brand) === true) {
            $device->setBrand($resultRaw->device->brand);
        }

        if ($this->isRealResult($resultRaw->device->model) === true) {
            $device->setModel($resultRaw->device->model);
        }

        return $result;
    }
}
