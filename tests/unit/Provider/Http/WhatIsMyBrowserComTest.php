<?php

namespace UserAgentParserTest\Unit\Provider\Http;

use GuzzleHttp\Psr7\Response;
use stdClass;
use UserAgentParser\Provider\Http\WhatIsMyBrowserCom;
use UserAgentParserTest\Unit\Provider\AbstractProviderTestCase;
use UserAgentParserTest\Unit\Provider\RequiredProviderTestInterface;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers \UserAgentParser\Provider\Http\WhatIsMyBrowserCom
 *
 * @internal
 */
class WhatIsMyBrowserComTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    public function testGetName()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertEquals('WhatIsMyBrowserCom', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertEquals('https://www.whatismybrowser.com/', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertEquals([
            'browser' => [
                'name' => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name' => true,
                'version' => true,
            ],

            'operatingSystem' => [
                'name' => true,
                'version' => true,
            ],

            'device' => [
                'model' => true,
                'brand' => true,
                'type' => true,
                'isMobile' => false,
                'isTouch' => false,
            ],

            'bot' => [
                'isBot' => true,
                'name' => true,
                'type' => true,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function testIsRealResult()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        // browser name
        $this->assertIsRealResult($provider, false, 'Unknown Mobile Browser', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'Unknown Mobile Browser something', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'something Unknown Mobile Browser', 'browser', 'name');

        $this->assertIsRealResult($provider, false, 'Unknown browser', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'Unknown browser something', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'something Unknown browser', 'browser', 'name');

        $this->assertIsRealResult($provider, false, 'Webkit based browser', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'Webkit based browser something', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'something Webkit based browser', 'browser', 'name');

        $this->assertIsRealResult($provider, false, 'a UNIX based OS', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'a UNIX based OS something', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'something a UNIX based OS', 'browser', 'name');

        // OS name
        $this->assertIsRealResult($provider, false, 'Smart TV', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'Smart TV something', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'something Smart TV', 'operatingSystem', 'name');

        // device model
        $this->assertIsRealResult($provider, false, 'HTC', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'HTC something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something HTC', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Mobile', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Mobile something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Mobile', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Android Phone', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Android Phone something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Android Phone', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Android Tablet', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Android Tablet something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Android Tablet', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Tablet', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Tablet something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Tablet', 'device', 'model');
    }

    /**
     * Empty user agent.
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionEmptyUserAgent()
    {
        $responseQueue = [
            new Response(200),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('');
    }

    /**
     * No JSON returned.
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

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $rawResult = new stdClass();
        $rawResult->message_code = 'no_user_agent';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');
    }

    /**
     * usage_limit_exceeded.
     *
     * @expectedException \UserAgentParser\Exception\LimitationExceededException
     */
    public function testParseLimitationExceededException()
    {
        $rawResult = new stdClass();
        $rawResult->message_code = 'usage_limit_exceeded';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * no_api_user_key.
     *
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     */
    public function testParseInvalidCredentialsExceptionNoKey()
    {
        $rawResult = new stdClass();
        $rawResult->message_code = 'no_api_user_key';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * user_key_invalid.
     *
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     */
    public function testParseInvalidCredentialsExceptionInvalidKey()
    {
        $rawResult = new stdClass();
        $rawResult->message_code = 'user_key_invalid';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * unknown.
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testParseRequestExceptionUnknown()
    {
        $rawResult = new stdClass();
        $rawResult->result = 'unknown';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * missing data.
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testParseRequestExceptionMissingData()
    {
        $rawResult = new stdClass();
        $rawResult->result = 'success';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = new stdClass();

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundExceptionDefaultValue()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->browser_name = 'Unknown browser';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundExceptionDefaultValue2()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->operating_platform = 'Mobile';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function testProviderNameAndVersionIsInResult()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->software_type = 'bot';
        $parseResult->browser_name = '360Spider';
        $parseResult->software_sub_type = 'crawler';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('WhatIsMyBrowserCom', $result->getProviderName());
        $this->assertNull($result->getProviderVersion());
    }

    /**
     * Bot.
     */
    public function testParseBot()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->software_type = 'bot';
        $parseResult->browser_name = '360Spider';
        $parseResult->software_sub_type = 'crawler';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name' => '360Spider',
                'type' => 'crawler',
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only.
     */
    public function testParseBrowser()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->browser_name = 'Firefox';
        $parseResult->browser_version_full = '3.2.1';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name' => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 2,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.2.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only.
     */
    public function testParseBrowserDefaultValue()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->browser_name = 'Unknown browser';
        $parseResult->layout_engine_name = 'Webkit';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'renderingEngine' => [
                'name' => 'Webkit',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Engine only.
     */
    public function testParseEngine()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->layout_engine_name = 'Webkit';
        $parseResult->layout_engine_version = '3.2.1';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'renderingEngine' => [
                'name' => 'Webkit',
                'version' => [
                    'major' => 3,
                    'minor' => 2,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.2.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * OS only.
     */
    public function testParseOperatingSystem()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->operating_system_name = 'BlackBerryOS';
        $parseResult->operating_system_version_full = '6.0.0';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name' => 'BlackBerryOS',
                'version' => [
                    'major' => 6,
                    'minor' => 0,
                    'patch' => 0,

                    'alias' => null,

                    'complete' => '6.0.0',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only.
     */
    public function testParseDeviceOnlyVendor()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->operating_platform_vendor_name = 'Dell';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => 'Dell',
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only.
     */
    public function testParseDevice()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->operating_platform = 'Galaxy Note';
        $parseResult->operating_platform_vendor_name = 'Dell';
        $parseResult->hardware_type = 'mobile';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'Galaxy Note',
                'brand' => 'Dell',
                'type' => 'mobile',

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only.
     */
    public function testParseDeviceDefaultValue()
    {
        $parseResult = new stdClass();
        $parseResult->user_agent = 'A real user agent...';
        $parseResult->operating_platform = 'Android Phone';
        $parseResult->operating_platform_vendor_name = 'Dell';

        $rawResult = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => 'Dell',
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
