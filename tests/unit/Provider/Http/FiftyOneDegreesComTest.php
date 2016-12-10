<?php
namespace UserAgentParserTest\Unit\Provider\Http;

use GuzzleHttp\Psr7\Response;
use stdClass;
use UserAgentParser\Provider\Http\FiftyOneDegreesCom;
use UserAgentParserTest\Unit\Provider\AbstractProviderTestCase;
use UserAgentParserTest\Unit\Provider\RequiredProviderTestInterface;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\Http\FiftyOneDegreesCom
 */
class FiftyOneDegreesComTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    public function testGetName()
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertEquals('FiftyOneDegreesCom', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertEquals('https://51degrees.com', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertEquals([

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
        ], $provider->getDetectionCapabilities());
    }

    public function testIsRealResult()
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'Unknown');
        $this->assertIsRealResult($provider, true, 'Unknown something');
        $this->assertIsRealResult($provider, true, 'something Unknown');
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

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $rawResult              = new stdClass();
        $rawResult->MatchMethod = 'None';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');
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

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * user_key_invalid
     *
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     */
    public function testParseInvalidCredentialsExceptionInvalidKey()
    {
        $rawResult              = new stdClass();
        $rawResult->MatchMethod = 'None';

        $responseQueue = [
            new Response(403, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * unknown
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testParseRequestExceptionUnknown()
    {
        $rawResult              = new stdClass();
        $rawResult->MatchMethod = 'None';

        $responseQueue = [
            new Response(500, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testParseRequestExceptionMissingData()
    {
        $rawResult              = new stdClass();
        $rawResult->MatchMethod = 'Direct';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $parseResult            = new stdClass();
        $parseResult->IsCrawler = [
            'True',
        ];

        $rawResult              = new stdClass();
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values      = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => null,
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
        $parseResult              = new stdClass();
        $parseResult->BrowserName = [
            'Firefox',
        ];
        $parseResult->BrowserVersion = [
            '3.2.1',
        ];

        $rawResult              = new stdClass();
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values      = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

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
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Engine only
     */
    public function testParseEngine()
    {
        $parseResult               = new stdClass();
        $parseResult->LayoutEngine = [
            'Webkit',
        ];

        $rawResult              = new stdClass();
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values      = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'renderingEngine' => [
                'name'    => 'Webkit',
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
     * OS only
     */
    public function testParseOperatingSystem()
    {
        $parseResult               = new stdClass();
        $parseResult->PlatformName = [
            'BlackBerryOS',
        ];
        $parseResult->PlatformVersion = [
            '6.0.0',
        ];

        $rawResult              = new stdClass();
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values      = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'BlackBerryOS',
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
     * Device only
     */
    public function testParseDevice()
    {
        $parseResult                 = new stdClass();
        $parseResult->HardwareVendor = [
            'Dell',
        ];
        $parseResult->HardwareFamily = [
            'Galaxy Note',
        ];
        $parseResult->DeviceType = [
            'mobile',
        ];
        $parseResult->IsMobile = [
            'True',
        ];

        $rawResult              = new stdClass();
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values      = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'Galaxy Note',
                'brand' => 'Dell',
                'type'  => 'mobile',

                'isMobile' => true,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
