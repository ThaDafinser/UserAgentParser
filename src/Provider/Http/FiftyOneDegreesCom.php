<?php
namespace UserAgentParser\Provider\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use stdClass;
use UserAgentParser\Exception;
use UserAgentParser\Model;

/**
 * Abstraction of neutrinoapi.com
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://51degrees.com
 */
class FiftyOneDegreesCom extends AbstractHttpProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'FiftyOneDegreesCom';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://51degrees.com';

    protected $detectionCapabilities = [

        'browser' => [
            'name'    => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name'    => true,
            'version' => false,
        ],

        'operatingSystem' => [
            'name'    => true,
            'version' => true,
        ],

        'device' => [
            'model'    => true,
            'brand'    => true,
            'type'     => true,
            'isMobile' => true,
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => true,
            'name'  => false,
            'type'  => false,
        ],
    ];

    protected $defaultValues = [
        'general' => [
            '/^Unknown$/i',
        ],
    ];

    private static $uri = 'https://cloud.51degrees.com/api/v1';

    private $apiKey;

    public function __construct(Client $client, $apiKey)
    {
        parent::__construct($client);

        $this->apiKey = $apiKey;
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

        $headers['User-Agent'] = $userAgent;

        $parameters = '/' . $this->apiKey;
        $parameters .= '/match?';

        $headerString = [];
        foreach ($headers as $key => $value) {
            $headerString[] = $key . '=' . rawurlencode($value);
        }

        $parameters .= implode('&', $headerString);

        $uri = self::$uri . $parameters;

        $request = new Request('GET', $uri);

        try {
            $response = $this->getResponse($request);
        } catch (Exception\RequestException $ex) {
            /* @var $prevEx \GuzzleHttp\Exception\ClientException */
            $prevEx = $ex->getPrevious();

            if ($prevEx->getResponse()->getStatusCode() === 403) {
                throw new Exception\InvalidCredentialsException('Your API key "' . $this->apiKey . '" is not valid for ' . $this->getName(), null, $ex);
            }

            throw $ex;
        }

        /*
         * no json returned?
         */
        $contentType = $response->getHeader('Content-Type');
        if (! isset($contentType[0]) || $contentType[0] != 'application/json; charset=utf-8') {
            throw new Exception\RequestException('Could not get valid "application/json; charset=utf-8" response from "' . $request->getUri() . '". Response is "' . $response->getBody()->getContents() . '"');
        }

        $content = json_decode($response->getBody()->getContents());

        /*
         * No result
         */
        if (isset($content->MatchMethod) && $content->MatchMethod == 'None') {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Missing data?
         */
        if (! $content instanceof stdClass || ! isset($content->Values)) {
            throw new Exception\RequestException('Could not get valid response from "' . $request->getUri() . '". Data is missing "' . $response->getBody()->getContents() . '"');
        }

        /*
         * Convert the values, to something useable
         */
        $values              = new \stdClass();
        $values->MatchMethod = $content->MatchMethod;

        foreach ($content->Values as $key => $value) {
            if (is_array($value) && count($value) === 1 && isset($value[0])) {
                $values->{$key} = $value[0];
            }
        }

        foreach ($values as $key => $value) {
            if ($value === 'True') {
                $values->{$key} = true;
            } elseif ($value === 'False') {
                $values->{$key} = false;
            }
        }

        return $values;
    }

    /**
     *
     * @param Model\Bot $bot
     * @param stdClass  $resultRaw
     */
    private function hydrateBot(Model\Bot $bot, stdClass $resultRaw)
    {
        $bot->setIsBot(true);
    }

    /**
     *
     * @param Model\Browser $browser
     * @param stdClass      $resultRaw
     */
    private function hydrateBrowser(Model\Browser $browser, stdClass $resultRaw)
    {
        if (isset($resultRaw->BrowserName)) {
            $browser->setName($this->getRealResult($resultRaw->BrowserName));
        }

        if (isset($resultRaw->BrowserVersion)) {
            $browser->getVersion()->setComplete($this->getRealResult($resultRaw->BrowserVersion));
        }
    }

    /**
     *
     * @param Model\RenderingEngine $engine
     * @param stdClass              $resultRaw
     */
    private function hydrateRenderingEngine(Model\RenderingEngine $engine, stdClass $resultRaw)
    {
        if (isset($resultRaw->LayoutEngine)) {
            $engine->setName($this->getRealResult($resultRaw->LayoutEngine));
        }
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param stdClass              $resultRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, stdClass $resultRaw)
    {
        if (isset($resultRaw->PlatformName)) {
            $os->setName($this->getRealResult($resultRaw->PlatformName));
        }

        if (isset($resultRaw->PlatformVersion)) {
            $os->getVersion()->setComplete($this->getRealResult($resultRaw->PlatformVersion));
        }
    }

    /**
     *
     * @param Model\Device $device
     * @param stdClass     $resultRaw
     */
    private function hydrateDevice(Model\Device $device, stdClass $resultRaw)
    {
        if (isset($resultRaw->HardwareVendor)) {
            $device->setBrand($this->getRealResult($resultRaw->HardwareVendor));
        }
        if (isset($resultRaw->HardwareFamily)) {
            $device->setModel($this->getRealResult($resultRaw->HardwareFamily));
        }
        if (isset($resultRaw->DeviceType)) {
            $device->setType($this->getRealResult($resultRaw->DeviceType));
        }
        if (isset($resultRaw->IsMobile)) {
            $device->setIsMobile($this->getRealResult($resultRaw->IsMobile));
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
        if (isset($resultRaw->IsCrawler) && $resultRaw->IsCrawler === true) {
            $this->hydrateBot($result->getBot(), $resultRaw);

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $resultRaw);
        $this->hydrateRenderingEngine($result->getRenderingEngine(), $resultRaw);
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultRaw);
        $this->hydrateDevice($result->getDevice(), $resultRaw);

        return $result;
    }
}
