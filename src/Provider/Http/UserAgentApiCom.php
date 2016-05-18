<?php
namespace UserAgentParser\Provider\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use stdClass;
use UserAgentParser\Exception;
use UserAgentParser\Model;

/**
 * Abstraction of useragentapi.com
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://useragentapi.com/docs
 */
class UserAgentApiCom extends AbstractHttpProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'UserAgentApiCom';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'http://useragentapi.com/';

    protected $detectionCapabilities = [

        'browser' => [
            'name'    => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name'    => true,
            'version' => true,
        ],

        'operatingSystem' => [
            'name'    => false,
            'version' => false,
        ],

        'device' => [
            'model'    => false,
            'brand'    => false,
            'type'     => true,
            'isMobile' => false,
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => true,
            'name'  => true,
            'type'  => false,
        ],
    ];

    private static $uri = 'https://useragentapi.com/api/v3/json';

    private $apiKey;

    public function __construct(Client $client, $apiKey)
    {
        parent::__construct($client);

        $this->apiKey = $apiKey;
    }

    public function getVersion()
    {
        return;
    }

    /**
     *
     * @param  string                     $userAgent
     * @param  array                      $headers
     * @return stdClass
     * @throws Exception\RequestException
     */
    protected function getResult($userAgent, array $headers)
    {
        /*
         * an empty UserAgent makes no sense
         */
        if ($userAgent == '') {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        $parameters = '/' . $this->apiKey;
        $parameters .= '/' . rawurlencode($userAgent);

        $uri = self::$uri . $parameters;

        $request = new Request('GET', $uri);

        try {
            $response = $this->getResponse($request);
        } catch (Exception\RequestException $ex) {
            /* @var $prevEx \GuzzleHttp\Exception\ClientException */
            $prevEx = $ex->getPrevious();

            if ($prevEx->hasResponse() === true && $prevEx->getResponse()->getStatusCode() === 400) {
                $content = $prevEx->getResponse()
                    ->getBody()
                    ->getContents();
                $content = json_decode($content);

                /*
                 * Error
                 */
                if (isset($content->error->code) && $content->error->code == 'key_invalid') {
                    throw new Exception\InvalidCredentialsException('Your API key "' . $this->apiKey . '" is not valid for ' . $this->getName(), null, $ex);
                }

                if (isset($content->error->code) && $content->error->code == 'useragent_invalid') {
                    throw new Exception\RequestException('User agent is invalid "' . $userAgent . '"');
                }
            }

            throw $ex;
        }

        /*
         * no json returned?
         */
        $contentType = $response->getHeader('Content-Type');
        if (! isset($contentType[0]) || $contentType[0] != 'application/json') {
            throw new Exception\RequestException('Could not get valid "application/json" response from "' . $request->getUri() . '". Response is "' . $response->getBody()->getContents() . '"');
        }

        $content = json_decode($response->getBody()->getContents());

        /*
         * No result
         */
        if (isset($content->error->code) && $content->error->code == 'useragent_not_found') {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Missing data?
         */
        if (! $content instanceof stdClass || ! isset($content->data)) {
            throw new Exception\RequestException('Could not get valid response from "' . $request->getUri() . '". Data is missing "' . $response->getBody()->getContents() . '"');
        }

        return $content->data;
    }

    /**
     *
     * @param  stdClass $resultRaw
     * @return boolean
     */
    private function isBot(stdClass $resultRaw)
    {
        if (isset($resultRaw->platform_type) && $resultRaw->platform_type === 'Bot') {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Bot $bot
     * @param stdClass  $resultRaw
     */
    private function hydrateBot(Model\Bot $bot, stdClass $resultRaw)
    {
        $bot->setIsBot(true);

        if (isset($resultRaw->platform_name)) {
            $bot->setName($this->getRealResult($resultRaw->platform_name));
        }
    }

    /**
     *
     * @param Model\Browser $browser
     * @param stdClass      $resultRaw
     */
    private function hydrateBrowser(Model\Browser $browser, stdClass $resultRaw)
    {
        if (isset($resultRaw->browser_name)) {
            $browser->setName($this->getRealResult($resultRaw->browser_name));
        }

        if (isset($resultRaw->browser_version)) {
            $browser->getVersion()->setComplete($this->getRealResult($resultRaw->browser_version));
        }
    }

    /**
     *
     * @param Model\RenderingEngine $engine
     * @param stdClass              $resultRaw
     */
    private function hydrateRenderingEngine(Model\RenderingEngine $engine, stdClass $resultRaw)
    {
        if (isset($resultRaw->engine_name)) {
            $engine->setName($this->getRealResult($resultRaw->engine_name));
        }

        if (isset($resultRaw->engine_version)) {
            $engine->getVersion()->setComplete($this->getRealResult($resultRaw->engine_version));
        }
    }

    /**
     *
     * @param Model\Device $device
     * @param stdClass     $resultRaw
     */
    private function hydrateDevice(Model\Device $device, stdClass $resultRaw)
    {
        if (isset($resultRaw->platform_type)) {
            $device->setType($this->getRealResult($resultRaw->platform_type));
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $resultRaw = $this->getResult($userAgent, $headers);

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultRaw);

        /*
         * Bot detection
         */
        if ($this->isBot($resultRaw) === true) {
            $this->hydrateBot($result->getBot(), $resultRaw);

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $resultRaw);
        $this->hydrateRenderingEngine($result->getRenderingEngine(), $resultRaw);
        $this->hydrateDevice($result->getDevice(), $resultRaw);

        return $result;
    }
}
