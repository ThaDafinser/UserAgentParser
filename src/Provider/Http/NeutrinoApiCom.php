<?php

namespace UserAgentParser\Provider\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use stdClass;
use UserAgentParser\Exception;
use UserAgentParser\Model;

/**
 * Abstraction of neutrinoapi.com.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @see https://www.neutrinoapi.com/api/user-agent-info/
 */
class NeutrinoApiCom extends AbstractHttpProvider
{
    /**
     * Name of the provider.
     *
     * @var string
     */
    protected $name = 'NeutrinoApiCom';

    /**
     * Homepage of the provider.
     *
     * @var string
     */
    protected $homepage = 'https://www.neutrinoapi.com/';

    protected $detectionCapabilities = [
        'browser' => [
            'name' => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name' => false,
            'version' => false,
        ],

        'operatingSystem' => [
            'name' => true,
            'version' => true,
        ],

        'device' => [
            'model' => true,
            'brand' => true,
            'type' => true,
            'isMobile' => true,
            'isTouch' => false,
        ],

        'bot' => [
            'isBot' => true,
            'name' => true,
            'type' => false,
        ],
    ];

    protected $defaultValues = [
        'general' => [
            '/^unknown$/i',
        ],

        'device' => [
            'brand' => [
                '/^Generic$/i',
                '/^generic web browser$/i',
            ],

            'model' => [
                '/^Android/i',
                '/^Windows Phone/i',
                '/^Windows Mobile/i',
                '/^Firefox/i',
                '/^Generic/i',
                '/^Tablet on Android$/i',
                '/^Tablet$/i',
            ],
        ],
    ];

    private static $uri = 'https://neutrinoapi.com/user-agent-info';

    private $apiUserId;

    private $apiKey;

    public function __construct(Client $client, $apiUserId, $apiKey)
    {
        parent::__construct($client);

        $this->apiUserId = $apiUserId;
        $this->apiKey = $apiKey;
    }

    public function getVersion()
    {
    }

    public function parse($userAgent, array $headers = [])
    {
        $resultRaw = $this->getResult($userAgent, $headers);

        // No result found?
        if ($this->hasResult($resultRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        // Hydrate the model
        $result = new Model\UserAgent($this->getName(), $this->getVersion());
        $result->setProviderResultRaw($resultRaw);

        // Bot detection
        if ($this->isBot($resultRaw) === true) {
            $this->hydrateBot($result->getBot(), $resultRaw);

            return $result;
        }

        // hydrate the result
        $this->hydrateBrowser($result->getBrowser(), $resultRaw);
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultRaw);
        $this->hydrateDevice($result->getDevice(), $resultRaw);

        return $result;
    }

    /**
     * @param string $userAgent
     *
     * @throws Exception\RequestException
     *
     * @return stdClass
     */
    protected function getResult($userAgent, array $headers)
    {
        // an empty UserAgent makes no sense
        if ($userAgent == '') {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        $params = [
            'user-id' => $this->apiUserId,
            'api-key' => $this->apiKey,
            'output-format' => 'json',
            'output-case' => 'snake',

            'user-agent' => $userAgent,
        ];

        $body = http_build_query($params, null, '&');

        $request = new Request('POST', self::$uri, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], $body);

        try {
            $response = $this->getResponse($request);
        } catch (Exception\RequestException $ex) {
            // @var $prevEx \GuzzleHttp\Exception\ClientException
            $prevEx = $ex->getPrevious();

            if ($prevEx->hasResponse() === true && $prevEx->getResponse()->getStatusCode() === 403) {
                throw new Exception\InvalidCredentialsException('Your API userId "' . $this->apiUserId . '" and key "' . $this->apiKey . '" is not valid for ' . $this->getName(), null, $ex);
            }

            throw $ex;
        }

        // no json returned?
        $contentType = $response->getHeader('Content-Type');
        if (!isset($contentType[0]) || $contentType[0] != 'application/json;charset=UTF-8') {
            throw new Exception\RequestException('Could not get valid "application/json" response from "' . $request->getUri() . '". Response is "' . $response->getBody()->getContents() . '"');
        }

        $content = json_decode($response->getBody()->getContents());

        // errors
        if (isset($content->api_error)) {
            switch ($content->api_error) {
                case 1:
                    throw new Exception\RequestException('"' . $content->api_error_msg . '" response from "' . $request->getUri() . '". Response is "' . print_r($content, true) . '"');

                    break;

                case 2:
                    throw new Exception\LimitationExceededException('Exceeded the maximum number of request with API userId "' . $this->apiUserId . '" and key "' . $this->apiKey . '" for ' . $this->getName());

                    break;

                default:
                    throw new Exception\RequestException('"' . $content->api_error_msg . '" response from "' . $request->getUri() . '". Response is "' . print_r($content, true) . '"');

                    break;
            }
        }

        // Missing data?
        if (!$content instanceof stdClass) {
            throw new Exception\RequestException('Could not get valid response from "' . $request->getUri() . '". Response is "' . $response->getBody()->getContents() . '"');
        }

        return $content;
    }

    /**
     * @return bool
     */
    private function hasResult(stdClass $resultRaw)
    {
        if (isset($resultRaw->type) && $this->isRealResult($resultRaw->type)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isBot(stdClass $resultRaw)
    {
        if (isset($resultRaw->type) && $resultRaw->type === 'robot') {
            return true;
        }

        return false;
    }

    private function hydrateBot(Model\Bot $bot, stdClass $resultRaw)
    {
        $bot->setIsBot(true);

        if (isset($resultRaw->browser_name)) {
            $bot->setName($this->getRealResult($resultRaw->browser_name));
        }
    }

    private function hydrateBrowser(Model\Browser $browser, stdClass $resultRaw)
    {
        if (isset($resultRaw->browser_name)) {
            $browser->setName($this->getRealResult($resultRaw->browser_name, 'browser', 'name'));
        }

        if (isset($resultRaw->version)) {
            $browser->getVersion()->setComplete($this->getRealResult($resultRaw->version));
        }
    }

    private function hydrateOperatingSystem(Model\OperatingSystem $os, stdClass $resultRaw)
    {
        if (isset($resultRaw->operating_system_family)) {
            $os->setName($this->getRealResult($resultRaw->operating_system_family));
        }

        if (isset($resultRaw->operating_system_version)) {
            $os->getVersion()->setComplete($this->getRealResult($resultRaw->operating_system_version));
        }
    }

    private function hydrateDevice(Model\Device $device, stdClass $resultRaw)
    {
        if (isset($resultRaw->mobile_model)) {
            $device->setModel($this->getRealResult($resultRaw->mobile_model, 'device', 'model'));
        }

        if (isset($resultRaw->mobile_brand)) {
            $device->setBrand($this->getRealResult($resultRaw->mobile_brand, 'device', 'brand'));
        }

        if (isset($resultRaw->type)) {
            $device->setType($this->getRealResult($resultRaw->type));
        }

        if (isset($resultRaw->is_mobile) && $resultRaw->is_mobile === true) {
            $device->setIsMobile(true);
        }
    }
}
