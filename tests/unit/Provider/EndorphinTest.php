<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\Endorphin;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers UserAgentParser\Provider\Endorphin
 */
class EndorphinTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser()
    {
        $parser = self::createMock('EndorphinStudio\Detector\DetectorResult');

        $parser->Browser = self::createMock('EndorphinStudio\Detector\Browser');
        $parser->OS      = self::createMock('EndorphinStudio\Detector\OS');
        $parser->Device  = self::createMock('EndorphinStudio\Detector\Device');
        $parser->Robot   = self::createMock('EndorphinStudio\Detector\Robot');

        return $parser;
    }

    public function testGetName()
    {
        $provider = new Endorphin();

        $this->assertEquals('Endorphin', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new Endorphin();

        $this->assertEquals('https://github.com/endorphin-studio/browser-detector', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new Endorphin();

        $this->assertEquals('endorphin-studio/browser-detector', $provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new Endorphin();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new Endorphin();

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new Endorphin();

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
                'model'    => false,
                'brand'    => false,
                'type'     => true,
                'isMobile' => false,
                'isTouch'  => false,
            ],

            'bot' => [
                'isBot' => true,
                'name'  => true,
                'type'  => true,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function testIsRealResult()
    {
        $provider = new Endorphin();

        /*
         * general
         */
        $this->assertIsRealResult($provider, true, 'something');
    }

    public function testParser()
    {
        $provider = new Endorphin();

        $this->assertInstanceOf('EndorphinStudio\Detector\DetectorResult', $provider->getParser(''));
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $parser = $this->getParser();

        $provider = new Endorphin();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function testProviderNameAndVersionIsInResult()
    {
        $parser = $this->getParser();
        $parser->Robot->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Google (Smartphone)'));
        $parser->Robot->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('Search Engine'));

        $provider = new Endorphin();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('Endorphin', $result->getProviderName());
        $this->assertRegExp('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $parser = $this->getParser();
        $parser->Robot->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Google (Smartphone)'));
        $parser->Robot->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('Search Engine'));

        $provider = new Endorphin();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => 'Google (Smartphone)',
                'type'  => 'Search Engine',
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only
     */
    public function testParseBrowser()
    {
        $parser = $this->getParser();
        $parser->Browser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Firefox'));
        $parser->Browser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('3.2.1'));

        $provider = new Endorphin();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

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
        $parser->OS->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Windows'));
        $parser->OS->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('7.0.1'));

        $provider = new Endorphin();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

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
        $parser->Device->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('iPhone'));
        $parser->Device->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('mobile'));

        $provider = new Endorphin();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'mobile',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
