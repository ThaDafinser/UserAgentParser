<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

/**
 * Abstraction for donatj/PhpUserAgent
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://github.com/donatj/PhpUserAgent
 */
class DonatjUAParser extends AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'DonatjUAParser';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/donatj/PhpUserAgent';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'donatj/phpuseragentparser';

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

    private $functionName = '\parse_user_agent';

    public function __construct()
    {
        if (! file_exists('vendor/' . $this->getPackageName() . '/composer.json')) {
            throw new PackageNotLoadedException('You need to install the package ' . $this->getPackageName() . ' to use this provider');
        }
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
        $browser->setName($this->getRealResult($resultRaw['browser']));
        $browser->getVersion()->setComplete($this->getRealResult($resultRaw['version']));
    }

    public function parse($userAgent, array $headers = [])
    {
        $functionName = $this->functionName;

        $resultRaw = $functionName($userAgent);

        if ($this->hasResult($resultRaw) !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
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
