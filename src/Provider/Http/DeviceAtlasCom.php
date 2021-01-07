<?php

namespace UserAgentParser\Provider\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use stdClass;
use UserAgentParser\Exception;
use UserAgentParser\Model;

/**
 * Abstraction of deviceatlas.com.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @see https://deviceatlas.com/resources/enterprise-api-documentation
 */
class DeviceAtlasCom extends AbstractHttpProvider
{
    /**
     * Name of the provider.
     *
     * @var string
     */
    protected $name = 'DeviceAtlasCom';

    /**
     * Homepage of the provider.
     *
     * @var string
     */
    protected $homepage = 'https://deviceatlas.com/';

    protected $detectionCapabilities = [
        'browser' => [
            'name' => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name' => true,
            'version' => false,
        ],

        'operatingSystem' => [
            'name' => true,
            'version' => true,
        ],

        'device' => [
            'model' => false,
            'brand' => false,
            'type' => true,
            'isMobile' => false,
            'isTouch' => false,
        ],

        'bot' => [
            'isBot' => false,
            'name' => false,
            'type' => false,
        ],
    ];

    private static $uri = 'http://region0.deviceatlascloud.com/v1/detect/properties';

    private $apiKey;

    public function __construct(Client $client, $apiKey)
    {
        parent::__construct($client);

        $this->apiKey = $apiKey;
    }

    public function parse($userAgent, array $headers = [])
    {
        $resultRaw = $this->getResult($userAgent, $headers);

        // Hydrate the model
        $result = new Model\UserAgent($this->getName(), $this->getVersion());
        $result->setProviderResultRaw($resultRaw);

        // hydrate the result
        $this->hydrateBrowser($result->getBrowser(), $resultRaw);
        $this->hydrateRenderingEngine($result->getRenderingEngine(), $resultRaw);
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultRaw);
        $this->hydrateDevice($result->getDevice(), $resultRaw);

        return $result;
    }

    protected function getResult($userAgent, array $headers)
    {
        // an empty UserAgent makes no sense
        if ($userAgent == '') {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        $parameters = '?licencekey=' . rawurlencode($this->apiKey);
        $parameters .= '&useragent=' . rawurlencode($userAgent);

        $uri = self::$uri . $parameters;

        // key to lower
        $headers = array_change_key_case($headers);

        $newHeaders = [];
        foreach ($headers as $key => $value) {
            $newHeaders['X-DA-' . $key] = $value;
        }

        $newHeaders['User-Agent'] = 'ThaDafinser/UserAgentParser:v1.4';

        $request = new Request('GET', $uri, $newHeaders);

        try {
            $response = $this->getResponse($request);
        } catch (Exception\RequestException $ex) {
            // @var $prevEx \GuzzleHttp\Exception\ClientException
            $prevEx = $ex->getPrevious();

            if ($prevEx->hasResponse() === true && $prevEx->getResponse()->getStatusCode() === 403) {
                throw new Exception\InvalidCredentialsException('Your API key "' . $this->apiKey . '" is not valid for ' . $this->getName(), null, $ex);
            }

            throw $ex;
        }

        // no json returned?
        $contentType = $response->getHeader('Content-Type');
        if (!isset($contentType[0]) || $contentType[0] != 'application/json; charset=UTF-8') {
            throw new Exception\RequestException('Could not get valid "application/json" response from "' . $request->getUri() . '". Response is "' . $response->getBody()->getContents() . '"');
        }

        $content = json_decode($response->getBody()->getContents());

        if (!$content instanceof stdClass || !isset($content->properties)) {
            throw new Exception\RequestException('Could not get valid response from "' . $request->getUri() . '". Response is "' . $response->getBody()->getContents() . '"');
        }

        // No result found?
        if (!$content->properties instanceof stdClass || \count((array) $content->properties) === 0) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        return $content->properties;
    }

    private function hydrateBrowser(Model\Browser $browser, stdClass $resultRaw)
    {
        if (isset($resultRaw->browserName)) {
            $browser->setName($this->getRealResult($resultRaw->browserName, 'browser', 'name'));
        }

        if (isset($resultRaw->browserVersion)) {
            $browser->getVersion()->setComplete($this->getRealResult($resultRaw->browserVersion, 'browser', 'version'));
        }
    }

    private function hydrateRenderingEngine(Model\RenderingEngine $engine, stdClass $resultRaw)
    {
        if (isset($resultRaw->browserRenderingEngine)) {
            $engine->setName($this->getRealResult($resultRaw->browserRenderingEngine));
        }
    }

    private function hydrateOperatingSystem(Model\OperatingSystem $os, stdClass $resultRaw)
    {
        if (isset($resultRaw->osName)) {
            $os->setName($this->getRealResult($resultRaw->osName));
        }

        if (isset($resultRaw->osVersion)) {
            $os->getVersion()->setComplete($this->getRealResult($resultRaw->osVersion));
        }
    }

    /**
     * @param Model\UserAgent $device
     */
    private function hydrateDevice(Model\Device $device, stdClass $resultRaw)
    {
        if (isset($resultRaw->primaryHardwareType)) {
            $device->setType($this->getRealResult($resultRaw->primaryHardwareType));
        }
    }
}
