<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\HandsetDetection;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers UserAgentParser\Provider\HandsetDetection
 */
class HandsetDetectionTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser()
    {
        $parser = self::createMock('HandsetDetection\HD4');

        return $parser;
    }

    public function testGetName()
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertEquals('HandsetDetection', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertEquals('https://github.com/HandsetDetection/php-apikit', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertEquals('handsetdetection/php-apikit', $provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new HandsetDetection($this->getParser());

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
                'type'     => false,
                'isMobile' => false,
                'isTouch'  => false,
            ],

            'bot' => [
                'isBot' => false,
                'name'  => false,
                'type'  => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function testIsRealResult()
    {
        $provider = new HandsetDetection($this->getParser());

        /*
         * general
         */
        $this->assertIsRealResult($provider, true, 'something');

        $this->assertIsRealResult($provider, false, 'generic', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'generic something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something generic', 'device', 'model');

        /*
         * device model
         */
        $this->assertIsRealResult($provider, false, 'analyzer', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'analyzer something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something analyzer', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'bot', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'bot something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something bot', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'crawler', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'crawler something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something crawler', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'library', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'library something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something library', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'spider', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'spider something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something spider', 'device', 'model');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(false);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultValue()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
            'hd_specs' => [
                'general_browser' => 'generic',
            ],
        ]);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\InvalidArgumentException
     */
    public function testParseInvalidArgumentException()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
            'status' => '299',
        ]);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function testProviderNameAndVersionIsInResult()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
            'hd_specs' => [
                'general_browser'         => 'Firefox',
                'general_browser_version' => '3.2.1',
            ],
        ]);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('HandsetDetection', $result->getProviderName());
        $this->assertRegExp('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Browser only
     */
    public function testParseBrowser()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
            'hd_specs' => [
                'general_browser'         => 'Firefox',
                'general_browser_version' => '3.2.1',
            ],
        ]);

        $provider = new HandsetDetection($parser);

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
     * OS only
     */
    public function testParseOperatingSystem()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
            'hd_specs' => [
                'general_platform'         => 'Windows',
                'general_platform_version' => '7.0.1',
            ],
        ]);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'Windows',
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
     * Device only
     */
    public function testParseDevice()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
            'hd_specs' => [
                'general_model'  => 'iPhone',
                'general_vendor' => 'Apple',
            ],
        ]);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => null,

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
