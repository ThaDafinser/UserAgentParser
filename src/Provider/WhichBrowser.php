<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;

class WhichBrowser extends AbstractProvider
{
    public function getName()
    {
        return 'WhichBrowser';
    }

    public function getComposerPackageName()
    {
        return 'whichbrowser/parser';
    }

    public function parse($userAgent, array $headers = [])
    {
        $headers['User-Agent'] = $userAgent;

        $parser = new \WhichBrowser\Parser($headers);

        /*
         * No result found?
         */
        if (!$parser->isDetected()) {
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
        if ($parser->isType('bot')) {
            $bot = $result->getBot();
            $bot->setIsBot(true);

            if ($name = $parser->browser->getName()) {
                $bot->setname($name);
            }
        }

        /*
         * Browser
         */
        $browser = $result->getBrowser();

        if ($name = $parser->browser->getName()) {
            $browser->setname($name);
        }

        if ($version = $parser->browser->getVersion()) {
            $browser->getVersion()->setComplete($version);
        }

        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();

        if ($name = $parser->engine->getName()) {
            $renderingEngine->setname($name);
        }

        if ($version = $parser->engine->getVersion()) {
            $renderingEngine->getVersion()->setComplete($version);
        }

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        if ($name = $parser->os->getName()) {
            $operatingSystem->setname($name);
        }

        if ($version = $parser->os->getVersion()) {
            $operatingSystem->getVersion()->setComplete($version);
        }

        /*
         * device
         */
        $device = $result->getDevice();

        if ($model = $parser->device->getModel()) {
            $device->setModel($model);
        }

        if ($model = $parser->device->getManufacturer()) {
            $device->setBrand($model);
        }

        $device->setType($parser->device->type);

        if ($parser->isType('mobile', 'tablet', 'ereader', 'media', 'watch', 'camera')) {
            $device->setIsMobile(true);
        }

        return $result;
    }
}
