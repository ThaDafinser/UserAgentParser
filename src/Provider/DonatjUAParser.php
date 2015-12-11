<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;

class DonatjUAParser extends AbstractProvider
{
    protected $detectionCapabilities = [

        'browser' => [
            'name'    => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name'    => false,
            'version' => false,
        ],

        'operatingSystem' => [
            'name'    => false,
            'version' => false,
        ],

        'device' => [
            'model'    => false,
            'brand'    => false,
            'type'     => false,
            'isMobile' => false,
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => false,
            'name'  => false,
            'type'  => false,
        ],
    ];

    public function __construct()
    {
        if (! function_exists('parse_user_agent')) {
            throw new Exception\PackageNotLoadedException('You need to install ' . $this->getComposerPackageName() . ' to use this provider');
        }
    }

    public function getName()
    {
        return 'DonatjUAParser';
    }

    public function getComposerPackageName()
    {
        return 'donatj/phpuseragentparser';
    }

    /**
     *
     * @param array $resultRaw
     *
     * @return bool
     */
    private function hasResult(array $resultRaw)
    {
        if ($this->isRealResult($resultRaw['browser'])) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Browser $browser
     * @param array         $resultRaw
     */
    private function hydrateBrowser(Model\Browser $browser, array $resultRaw)
    {
        if ($this->isRealResult($resultRaw['browser']) === true) {
            $browser->setName($resultRaw['browser']);
        }

        if ($this->isRealResult($resultRaw['version']) === true) {
            $browser->getVersion()->setComplete($resultRaw['version']);
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $resultRaw = parse_user_agent($userAgent);

        if ($this->hasResult($resultRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultRaw);

        /*
         * Bot detection - is currently not possible!
         */

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $resultRaw);
        // renderingEngine not available
        // os is mixed with device informations
        // device is mixed with os

        return $result;
    }
}
