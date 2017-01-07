<?php
namespace UserAgentParser\Provider\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use stdClass;
use UserAgentParser\Exception;
use UserAgentParser\Model;

/**
 * Abstraction of useragentstring.com
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @see https://developers.whatismybrowser.com/reference
 */
class WhatIsMyBrowserCom extends AbstractHttpProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'WhatIsMyBrowserCom';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://www.whatismybrowser.com/';

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
            'name'    => true,
            'version' => true,
        ],

        'device' => [
            'model' => true,
            'brand' => true,

            'type'     => true,
            'isMobile' => false,
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => true,
            'name'  => true,
            'type'  => true,
        ],
    ];

    protected $defaultValues = [

        'general' => [],

        'browser' => [
            'name' => [
                '/^Unknown Mobile Browser$/i',
                '/^Unknown browser$/i',
                '/^Webkit based browser$/i',
                '/^a UNIX based OS$/i',
            ],
        ],

        'operatingSystem' => [
            'name' => [
                '/^Smart TV$/i',
            ],
        ],

        'device' => [
            'model' => [
                // HTC generic or large parser error (over 1000 found)
                '/^HTC$/i',
                '/^Mobile$/i',
                '/^Android Phone$/i',
                '/^Android Tablet$/i',
                '/^Tablet$/i',
            ],
        ],
    ];

    private static $uri = 'http://api.whatismybrowser.com/api/v1/user_agent_parse';

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
     * @param string $userAgent
     * @param array  $headers
     *
     * @return stdClass
     * @throws Exception\InvalidCredentialsException
     * @throws Exception\LimitationExceededException
     * @throws Exception\NoResultFoundException
     * @throws Exception\RequestException
     */
    public function getResult($userAgent, array $headers)
    {
        /*
         * an empty UserAgent makes no sense
         */
        if ($userAgent == '') {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        $params = [
            'user_key'   => $this->apiKey,
            'user_agent' => $userAgent,
        ];

        $body = http_build_query($params, null, '&');

        $request = new Request('POST', self::$uri, [], $body);

        $response = $this->getResponse($request);

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
        if (isset($content->message_code) && $content->message_code == 'no_user_agent') {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Limit exceeded
         */
        if (isset($content->message_code) && $content->message_code == 'usage_limit_exceeded') {
            throw new Exception\LimitationExceededException('Exceeded the maximum number of request with API key "' . $this->apiKey . '" for ' . $this->getName());
        }

        /*
         * Error
         */
        if (isset($content->message_code) && $content->message_code == 'no_api_user_key') {
            throw new Exception\InvalidCredentialsException('Missing API key for ' . $this->getName());
        }

        if (isset($content->message_code) && $content->message_code == 'user_key_invalid') {
            throw new Exception\InvalidCredentialsException('Your API key "' . $this->apiKey . '" is not valid for ' . $this->getName());
        }

        if (! isset($content->result) || $content->result !== 'success') {
            throw new Exception\RequestException('Could not get valid response from "' . $request->getUri() . '". Response is "' . $response->getBody()->getContents() . '"');
        }

        /*
         * Missing data?
         */
        if (! $content instanceof stdClass || ! isset($content->parse) || ! $content->parse instanceof stdClass) {
            throw new Exception\RequestException('Could not get valid response from "' . $request->getUri() . '". Response is "' . print_r($content, true) . '"');
        }

        return $content->parse;
    }

    /**
     *
     * @param stdClass $resultRaw
     *
     * @return bool
     */
    private function hasResult(stdClass $resultRaw)
    {
        if (isset($resultRaw->browser_name) && $this->isRealResult($resultRaw->browser_name, 'browser', 'name') === true) {
            return true;
        }

        if (isset($resultRaw->layout_engine_name) && $this->isRealResult($resultRaw->layout_engine_name) === true) {
            return true;
        }

        if (isset($resultRaw->operating_system_name) && $this->isRealResult($resultRaw->operating_system_name, 'operatingSystem', 'name') === true) {
            return true;
        }

        if (isset($resultRaw->operating_platform) && $this->isRealResult($resultRaw->operating_platform, 'device', 'model') === true) {
            return true;
        }

        if (isset($resultRaw->operating_platform_vendor_name) && $this->isRealResult($resultRaw->operating_platform_vendor_name) === true) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param  stdClass $resultRaw
     * @return boolean
     */
    private function isBot(stdClass $resultRaw)
    {
        if (isset($resultRaw->software_type) && $resultRaw->software_type === 'bot') {
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

        if (isset($resultRaw->browser_name)) {
            $bot->setName($this->getRealResult($resultRaw->browser_name));
        }

        if (isset($resultRaw->software_sub_type)) {
            $bot->setType($this->getRealResult($resultRaw->software_sub_type));
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
            $browser->setName($this->getRealResult($resultRaw->browser_name, 'browser', 'name'));
        }

        if (isset($resultRaw->browser_version_full)) {
            $browser->getVersion()->setComplete($this->getRealResult($resultRaw->browser_version_full));
        }
    }

    /**
     *
     * @param Model\RenderingEngine $engine
     * @param stdClass              $resultRaw
     */
    private function hydrateRenderingEngine(Model\RenderingEngine $engine, stdClass $resultRaw)
    {
        if (isset($resultRaw->layout_engine_name)) {
            $engine->setName($this->getRealResult($resultRaw->layout_engine_name));
        }

        if (isset($resultRaw->layout_engine_version)) {
            $engine->getVersion()->setComplete($this->getRealResult($resultRaw->layout_engine_version));
        }
    }

    /**
     *
     * @param Model\OperatingSystem $os
     * @param stdClass              $resultRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, stdClass $resultRaw)
    {
        if (isset($resultRaw->operating_system_name)) {
            $os->setName($this->getRealResult($resultRaw->operating_system_name, 'operatingSystem', 'name'));
        }

        if (isset($resultRaw->operating_system_version_full)) {
            $os->getVersion()->setComplete($this->getRealResult($resultRaw->operating_system_version_full));
        }
    }

    /**
     *
     * @param Model\Device $device
     * @param stdClass     $resultRaw
     */
    private function hydrateDevice(Model\Device $device, stdClass $resultRaw)
    {
        if (isset($resultRaw->operating_platform)) {
            $device->setModel($this->getRealResult($resultRaw->operating_platform, 'device', 'model'));
        }

        if (isset($resultRaw->operating_platform_vendor_name)) {
            $device->setBrand($this->getRealResult($resultRaw->operating_platform_vendor_name));
        }

        if (isset($resultRaw->hardware_type)) {
            $device->setType($this->getRealResult($resultRaw->hardware_type));
        }
    }

    public function parse($userAgent, array $headers = [])
    {
        $resultRaw = $this->getResult($userAgent, $headers);

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
