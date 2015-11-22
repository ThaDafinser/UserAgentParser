<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;
use WhichBrowser\Parser as WhichBrowserParser;

class WhichBrowser extends AbstractProvider
{
    /**
     * Used for unitTests mocking
     * @var WhichBrowserParser
     */
    private $parser;

    public function getName()
    {
        return 'WhichBrowser';
    }

    public function getComposerPackageName()
    {
        return 'whichbrowser/parser';
    }

    /**
     * @param  array              $headers
     * @return WhichBrowserParser
     */
    public function getParser(array $headers)
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        return new WhichBrowserParser($headers);
    }

    public function parse($userAgent, array $headers = [])
    {
        $headers['User-Agent'] = $userAgent;

        $parser = $this->getParser($headers);

        /*
         * No result found?
         */
        if ($parser->isDetected() !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($parser->toArray());

        /*
         * Bot detection
         */
        if ($parser->isType('bot') === true) {
            $bot = $result->getBot();
            $bot->setIsBot(true);

            $name = $parser->browser->getName();
            if ($name !== '') {
                $bot->setName($name);
            }

            return $result;
        }

        /*
         * Browser
         */
        $browser = $result->getBrowser();

        $name = $parser->browser->getName();
        if ($name !== '') {
            $browser->setName($name);
        }

        $version = $parser->browser->getVersion();
        if ($version !== '') {
            $browser->getVersion()->setComplete($version);
        }

        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();

        $name = $parser->engine->getName();
        if ($name !== '') {
            $renderingEngine->setName($name);
        }

        $version = $parser->engine->getVersion();
        if ($version !== '') {
            $renderingEngine->getVersion()->setComplete($version);
        }

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        $name = $parser->os->getName();
        if ($name !== '') {
            $operatingSystem->setName($name);
        }

        $version = $parser->os->getVersion();
        if ($version !== '') {
            $operatingSystem->getVersion()->setComplete($version);
        }

        /*
         * device
         */
        $device = $result->getDevice();

        $model = $parser->device->getModel();
        if ($model !== '') {
            $device->setModel($model);
        }

        $brand = $parser->device->getManufacturer();
        if ($brand !== '') {
            $device->setBrand($brand);
        }

        $device->setType($parser->getType());

        if ($parser->isType('mobile', 'tablet', 'ereader', 'media', 'watch', 'camera', 'gaming:portable') === true) {
            $device->setIsMobile(true);
        }

        return $result;
    }
}
