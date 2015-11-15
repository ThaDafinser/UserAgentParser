<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;
use WhichBrowser\Parser as WhichBrowserParser;

class WhichBrowser extends AbstractProvider
{
    public function getName()
    {
        return 'WhichBrowser';
    }

    public function getComposerPackageName()
    {
        return 'whichbrowser/whichbrowser';
    }

    /**
     *
     * @param array $resultRaw
     *
     * @return bool
     */
    private function hasResult(array $resultRaw)
    {
        if (count($resultRaw) === 0) {
            return false;
        }

        return true;
    }

    private function isBot(array $resultRaw)
    {
        if (isset($resultRaw['device']['type']) && $resultRaw['device']['type'] === 'bot') {
            return true;
        }

        return false;
    }

    /**
     *
     * @param array $resultRaw
     *
     * @return bool
     */
    private function isMobile(array $resultRaw)
    {
        if (! isset($resultRaw['device']['type'])) {
            return false;
        }

        /*
         * Available types...
         *
         * TYPE_DESKTOP
         * TYPE_MOBILE
         * TYPE_DECT
         * TYPE_TABLET
         * TYPE_GAMING
         * TYPE_EREADER
         * TYPE_MEDIA
         * TYPE_HEADSET
         * TYPE_WATCH
         * TYPE_EMULATOR
         * TYPE_TELEVISION
         * TYPE_MONITOR
         * TYPE_CAMERA
         * TYPE_SIGNAGE
         * TYPE_WHITEBOARD
         */

        if ($resultRaw['device']['type'] === TYPE_MOBILE) {
            return true;
        }

        if ($resultRaw['device']['type'] === TYPE_TABLET) {
            return true;
        }

        if ($resultRaw['device']['type'] === TYPE_EREADER) {
            return true;
        }

        if ($resultRaw['device']['type'] === TYPE_MEDIA) {
            return true;
        }

        if ($resultRaw['device']['type'] === TYPE_WATCH) {
            return true;
        }

        if ($resultRaw['device']['type'] === TYPE_CAMERA) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param unknown $versionPart
     *
     * @return string
     */
    private function getVersionString($versionPart)
    {
        if (! is_array($versionPart)) {
            return $versionPart;
        }

        $version = null;

        if (isset($versionPart['alias'])) {
            $version = $versionPart['alias'];
        } elseif (isset($versionPart['value'])) {
            $version = $versionPart['value'];
        }

        if (isset($versionPart['nickname'])) {
            $version .= ' ' . $versionPart['nickname'];
        }

        return $version;
    }

    public function parse($userAgent, array $headers = [])
    {
        $headers['User-Agent'] = $userAgent;

        $parser = new WhichBrowserParser([
            'headers' => $headers,
        ]);

        $resultRaw = $parser->toArray();

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

            if (isset($resultRaw['browser']['name'])) {
                $bot->setName($resultRaw['browser']['name']);
            }

            return $result;
        }

        /*
         * Browser
         */
        $browser = $result->getBrowser();

        if (isset($resultRaw['browser']['alias'])) {
            $browser->setName($resultRaw['browser']['name']);
        } elseif (isset($resultRaw['browser']['name'])) {
            $browser->setName($resultRaw['browser']['name']);
        }

        if (isset($resultRaw['browser']['version'])) {
            $browser->getVersion()->setComplete($this->getVersionString($resultRaw['browser']['version']));
        }

        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();

        if (isset($resultRaw['engine']['name'])) {
            $renderingEngine->setName($resultRaw['engine']['name']);
        }

        if (isset($resultRaw['engine']['version'])) {
            $renderingEngine->getVersion()->setComplete($resultRaw['engine']['version']);
        }

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        if (isset($resultRaw['os']['name'])) {
            $operatingSystem->setName($resultRaw['os']['name']);
        }

        if (isset($resultRaw['os']['version'])) {
            $operatingSystem->getVersion()->setComplete($this->getVersionString($resultRaw['os']['version']));
        }

        /*
         * device
         */
        $device = $result->getDevice();

        if (isset($resultRaw['device']['model'])) {
            $model = $resultRaw['device']['model'];

            if (isset($resultRaw['device']['series'])) {
                $model .= ' ' . $resultRaw['device']['series'];
            }

            $device->setModel($model);
        }

        if (isset($resultRaw['device']['manufacturer'])) {
            $device->setBrand($resultRaw['device']['manufacturer']);
        }

        if (isset($resultRaw['device']['type'])) {
            $device->setType($resultRaw['device']['type']);
        }

        if ($this->isMobile($resultRaw) === true) {
            $device->setIsMobile(true);
        }

        return $result;
    }
}
