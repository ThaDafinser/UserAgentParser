<?php

namespace UserAgentParserTest\Unit\Provider;

use PHPUnit_Framework_MockObject_MockObject;
use UAParser\Result;
use UserAgentParser\Provider\UAParser;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers \UserAgentParser\Provider\UAParser
 *
 * @internal
 */
class UAParserTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    public function testGetName()
    {
        $provider = new UAParser();

        $this->assertEquals('UAParser', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new UAParser();

        $this->assertEquals('https://github.com/ua-parser/uap-php', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new UAParser();

        $this->assertEquals('ua-parser/uap-php', $provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new UAParser();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new UAParser();

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new UAParser();

        $this->assertEquals([
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
                'type' => false,
                'isMobile' => false,
                'isTouch' => false,
            ],

            'bot' => [
                'isBot' => true,
                'name' => true,
                'type' => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function testIsRealResult()
    {
        $provider = new UAParser();

        // general
        $this->assertIsRealResult($provider, false, 'Other');
        $this->assertIsRealResult($provider, true, 'Other something');
        $this->assertIsRealResult($provider, true, 'something Other');

        // device brand
        $this->assertIsRealResult($provider, false, 'Generic', 'device', 'brand');
        $this->assertIsRealResult($provider, false, 'Generic something', 'device', 'brand');
        $this->assertIsRealResult($provider, true, 'something Generic', 'device', 'brand');

        $this->assertIsRealResult($provider, false, 'unknown', 'device', 'brand');
        $this->assertIsRealResult($provider, true, 'unknown something', 'device', 'brand');
        $this->assertIsRealResult($provider, true, 'something unknown', 'device', 'brand');

        // device model
        $this->assertIsRealResult($provider, false, 'generic', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'generic something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something generic', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Smartphone', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Smartphone something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Smartphone', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Feature Phone', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Feature Phone something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Feature Phone', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'iOS-Device', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'iOS-Device something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something iOS-Device', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Tablet', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Tablet something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Tablet', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Touch', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Touch something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Touch', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Windows', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Windows something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Windows', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Windows Phone', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Windows Phone something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Windows Phone', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Android', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Android something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Android', 'device', 'model');

        // bot name
        $this->assertIsRealResult($provider, false, 'Other', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'Other something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something Other', 'bot', 'name');

        $this->assertIsRealResult($provider, false, 'crawler', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'crawler something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something crawler', 'bot', 'name');

        $this->assertIsRealResult($provider, false, 'robot', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'robot something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something robot', 'bot', 'name');

        $this->assertIsRealResult($provider, false, 'crawl', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'crawl something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something crawl', 'bot', 'name');

        $this->assertIsRealResult($provider, false, 'Spider', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'Spider something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something Spider', 'bot', 'name');
    }

    public function testParser()
    {
        $provider = new UAParser();
        $this->assertInstanceOf('UAParser\Parser', $provider->getParser());

        $parser = $this->getParser();

        $provider = new UAParser($parser);

        $this->assertSame($parser, $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $parser = $this->getParser($this->getResultMock());

        $provider = new UAParser($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultValue()
    {
        $result = $this->getResultMock();
        $result->ua->family = 'Other';

        $parser = $this->getParser($result);

        $provider = new UAParser($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultValueDeviceModel()
    {
        $result = $this->getResultMock();
        $result->device->model = 'Smartphone';

        $parser = $this->getParser($result);

        $provider = new UAParser($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function testProviderNameAndVersionIsInResult()
    {
        $result = $this->getResultMock();
        $result->device->family = 'Spider';
        $result->ua->family = 'Googlebot';

        $parser = $this->getParser($result);

        $provider = new UAParser($parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('UAParser', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot.
     */
    public function testParseBot()
    {
        $result = $this->getResultMock();
        $result->device->family = 'Spider';
        $result->ua->family = 'Googlebot';

        $parser = $this->getParser($result);

        $provider = new UAParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name' => 'Googlebot',
                'type' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Bot - default value.
     */
    public function testParseBotDefaultValue()
    {
        $result = $this->getResultMock();
        $result->device->family = 'Spider';
        $result->ua->family = 'Other';

        $parser = $this->getParser($result);

        $provider = new UAParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name' => null,
                'type' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only.
     */
    public function testParseBrowser()
    {
        $result = $this->getResultMock();
        $result->ua->family = 'Firefox';
        $result->ua->major = 3;
        $result->ua->minor = 2;
        $result->ua->patch = 1;

        $parser = $this->getParser($result);

        $provider = new UAParser($parser);

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
     * OS only.
     */
    public function testParseOperatingSystem()
    {
        $result = $this->getResultMock();
        $result->os->family = 'Windows';
        $result->os->major = 7;
        $result->os->minor = 0;
        $result->os->patch = 1;

        $parser = $this->getParser($result);

        $provider = new UAParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name' => 'Windows',
                'version' => [
                    'major' => 7,
                    'minor' => 0,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '7.0.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only.
     */
    public function testParseDevice()
    {
        $result = $this->getResultMock();
        $result->device->model = 'iPhone';
        $result->device->brand = 'Apple';

        $parser = $this->getParser($result);

        $provider = new UAParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device - default value.
     */
    public function testParseDeviceDefaultValue()
    {
        $result = $this->getResultMock();
        $result->os->family = 'Windows';
        $result->os->major = 7;

        $result->device->model = 'Feature Phone';
        $result->device->brand = 'Generic';

        $parser = $this->getParser($result);

        $provider = new UAParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name' => 'Windows',
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
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * @return \UAParser\Result\Client
     */
    private function getResultMock()
    {
        $ua = new Result\UserAgent();
        $os = new Result\OperatingSystem();
        $device = new Result\Device();

        $client = new Result\Client('');
        $client->ua = $ua;
        $client->os = $os;
        $client->device = $device;

        return $client;
    }

    /**
     * @param null|mixed $returnValue
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser($returnValue = null)
    {
        $parser = self::createMock('UAParser\Parser');
        $parser->expects($this->any())
            ->method('parse')
            ->willReturn($returnValue);

        return $parser;
    }
}
