<?php

namespace UserAgentParserTest\Unit\Provider;

use DeviceDetector\DeviceDetector;
use PHPUnit_Framework_MockObject_MockObject;
use UserAgentParser\Provider\PiwikDeviceDetector;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers \UserAgentParser\Provider\PiwikDeviceDetector
 *
 * @internal
 */
class PiwikDeviceDetectorTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    public function testGetName()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertEquals('PiwikDeviceDetector', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertEquals('https://github.com/piwik/device-detector', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertEquals('piwik/device-detector', $provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertEquals([
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
                'model' => true,
                'brand' => true,
                'type' => true,
                'isMobile' => true,
                'isTouch' => true,
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
        $provider = new PiwikDeviceDetector();

        // general
        $this->assertIsRealResult($provider, false, 'UNK');
        $this->assertIsRealResult($provider, true, 'UNK something');
        $this->assertIsRealResult($provider, true, 'something UNK');

        // bot name
        $this->assertIsRealResult($provider, false, 'Bot', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'Bot something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something Bot', 'bot', 'name');

        $this->assertIsRealResult($provider, false, 'Generic Bot', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'Generic Bot something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something Generic Bot', 'bot', 'name');
    }

    public function testParser()
    {
        $provider = new PiwikDeviceDetector();
        $this->assertInstanceOf('DeviceDetector\DeviceDetector', $provider->getParser());

        $parser = $this->getParser();

        $provider = new PiwikDeviceDetector($parser);

        $this->assertSame($parser, $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $parser = $this->getParser();

        $provider = new PiwikDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultValue()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getClient')
            ->willReturn([
                'name' => 'UNK',
            ]);

        $provider = new PiwikDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function testProviderNameAndVersionIsInResult()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isBot')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getBot')
            ->willReturn([
                'name' => 'Hatena RSS',
                'category' => 'something',
            ]);

        $provider = new PiwikDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('PiwikDeviceDetector', $result->getProviderName());
        $this->assertRegExp('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot.
     */
    public function testParseBot()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isBot')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getBot')
            ->willReturn([
                'name' => 'Hatena RSS',
                'category' => 'something',
            ]);

        $provider = new PiwikDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name' => 'Hatena RSS',
                'type' => 'something',
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Bot - name default.
     */
    public function testParseBotNameDefault()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isBot')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getBot')
            ->willReturn([
                'name' => 'Bot',
            ]);

        $provider = new PiwikDeviceDetector($parser);

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
     * Bot - name default.
     */
    public function testParseBotNameDefault2()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isBot')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getBot')
            ->willReturn([
                'name' => 'Generic Bot',
            ]);

        $provider = new PiwikDeviceDetector($parser);

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
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getClient')
            ->willReturn([
                'name' => 'Firefox',
                'version' => '3.0',
                'engine' => 'WebKit',
            ]);
        $parser->expects($this->any())
            ->method('getOs')
            ->willReturn([]);

        $provider = new PiwikDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name' => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '3.0',
                ],
            ],

            'renderingEngine' => [
                'name' => 'WebKit',
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
     * OS only.
     */
    public function testParseOperatingSystem()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getClient')
            ->willReturn([
                'engine' => DeviceDetector::UNKNOWN,
            ]);
        $parser->expects($this->any())
            ->method('getOs')
            ->willReturn([
                'name' => 'Windows',
                'version' => '7.0',
            ]);

        $provider = new PiwikDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name' => 'Windows',
                'version' => [
                    'major' => 7,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '7.0',
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
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getClient')
            ->willReturn([]);
        $parser->expects($this->any())
            ->method('getOs')
            ->willReturn([]);

        $parser->expects($this->any())
            ->method('getDevice')
            ->willReturn(1);

        $parser->expects($this->any())
            ->method('getModel')
            ->willReturn('iPhone');
        $parser->expects($this->any())
            ->method('getBrandName')
            ->willReturn('Apple');
        $parser->expects($this->any())
            ->method('getDeviceName')
            ->willReturn('smartphone');

        $parser->expects($this->any())
            ->method('isMobile')
            ->willReturn(true);

        $parser->expects($this->any())
            ->method('isTouchEnabled')
            ->willReturn(true);

        $provider = new PiwikDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type' => 'smartphone',

                'isMobile' => true,
                'isTouch' => true,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser()
    {
        $parser = self::createMock('DeviceDetector\DeviceDetector');

        return $parser;
    }
}
