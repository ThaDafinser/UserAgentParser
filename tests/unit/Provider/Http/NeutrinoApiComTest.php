<?php
namespace UserAgentParserTest\Unit\Provider\Http;

use GuzzleHttp\Psr7\Response;
use stdClass;
use UserAgentParser\Provider\Http\NeutrinoApiCom;
use UserAgentParserTest\Unit\Provider\AbstractProviderTestCase;
use UserAgentParserTest\Unit\Provider\RequiredProviderTestInterface;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\Http\NeutrinoApiCom
 */
class NeutrinoApiComTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    public function testGetName()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertEquals('NeutrinoApiCom', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertEquals('https://www.neutrinoapi.com/', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertNull($provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertNull($provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertNull($provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertEquals([

            'browser' => [
                'name'    => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name'    => false,
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
                'name'  => true,
                'type'  => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function testIsRealResult()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'unknown');
        $this->assertIsRealResult($provider, true, 'unknown something');
        $this->assertIsRealResult($provider, true, 'something unknown');

        /*
         * device brand
         */
        $this->assertIsRealResult($provider, false, 'Generic', 'device', 'brand');
        $this->assertIsRealResult($provider, true, 'Generic something', 'device', 'brand');
        $this->assertIsRealResult($provider, true, 'something Generic', 'device', 'brand');

        $this->assertIsRealResult($provider, false, 'generic web browser', 'device', 'brand');
        $this->assertIsRealResult($provider, true, 'generic web browser something', 'device', 'brand');
        $this->assertIsRealResult($provider, true, 'something generic web browser', 'device', 'brand');

        /*
         * device model
         */
        $this->assertIsRealResult($provider, false, 'Android', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Android something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Android', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Windows Phone', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Windows Phone something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Windows Phone', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Windows Mobile', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Windows Mobile something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Windows Mobile', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Firefox', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Firefox something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Firefox', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Generic', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Generic something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something AndGenericroid', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Tablet on Android', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Tablet on Android something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Tablet on Android', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Tablet', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Tablet something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Tablet', 'device', 'model');
    }

    /**
     * Empty user agent
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionEmptyUserAgent()
    {
        $responseQueue = [
            new Response(200),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('');
    }

    /**
     * 403
     *
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     */
    public function testParseInvalidCredentialsException()
    {
        $responseQueue = [
            new Response(403),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * 500
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testParseRequestException()
    {
        $responseQueue = [
            new Response(500),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * No JSON returned
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testParseRequestExceptionContentType()
    {
        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'text/html',
            ], 'something'),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Error code 1
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testParseRequestExceptionCode1()
    {
        $rawResult                = new stdClass();
        $rawResult->api_error     = 1;
        $rawResult->api_error_msg = 'something';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Error code 2
     *
     * @expectedException \UserAgentParser\Exception\LimitationExceededException
     */
    public function testParseLimitationExceededExceptionCode2()
    {
        $rawResult                = new stdClass();
        $rawResult->api_error     = 2;
        $rawResult->api_error_msg = 'something';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Error code something
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testParseRequestExceptionCodeSomething()
    {
        $rawResult                = new stdClass();
        $rawResult->api_error     = 1337;
        $rawResult->api_error_msg = 'something';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Missing data
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testParseRequestExceptionNoData()
    {
        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], ''),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * no result found
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $rawResult       = new stdClass();
        $rawResult->type = 'unknown';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function testProviderNameAndVersionIsInResult()
    {
        $rawResult               = new stdClass();
        $rawResult->type         = 'robot';
        $rawResult->browser_name = 'Googlebot';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('NeutrinoApiCom', $result->getProviderName());
        $this->assertNull($result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $rawResult               = new stdClass();
        $rawResult->type         = 'robot';
        $rawResult->browser_name = 'Googlebot';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => 'Googlebot',
                'type'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only
     */
    public function testParseBrowser()
    {
        $rawResult               = new stdClass();
        $rawResult->type         = 'desktop-browser';
        $rawResult->browser_name = 'Firefox';
        $rawResult->version      = '3.2.1';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 2,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.2.1',
                ],
            ],

            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'desktop-browser',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * OS only
     */
    public function testParseOperatingSystem()
    {
        $rawResult                           = new stdClass();
        $rawResult->type                     = 'desktop-browser';
        $rawResult->operating_system_family  = 'Windows';
        $rawResult->operating_system_version = '7';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'Windows',
                'version' => [
                    'major' => 7,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '7',
                ],
            ],

            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'desktop-browser',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only
     */
    public function testParseDevice()
    {
        $rawResult               = new stdClass();
        $rawResult->type         = 'mobile-browser';
        $rawResult->mobile_model = 'iPhone';
        $rawResult->mobile_brand = 'Apple';
        $rawResult->is_mobile    = true;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => 'mobile-browser',

                'isMobile' => true,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device - default value
     */
    public function testParseDeviceDefaultValue()
    {
        $rawResult               = new stdClass();
        $rawResult->type         = 'mobile-browser';
        $rawResult->mobile_model = 'Android';
        $rawResult->mobile_brand = 'Generic';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'mobile-browser',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
