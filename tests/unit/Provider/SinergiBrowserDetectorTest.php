<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\SinergiBrowserDetector;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\SinergiBrowserDetector
 */
class SinergiBrowserDetectorTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getBrowserParser()
    {
        $parser = self::createMock('Sinergi\BrowserDetector\Browser');

        return $parser;
    }

    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getOsParser()
    {
        $parser = self::createMock('Sinergi\BrowserDetector\Os');

        return $parser;
    }

    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDeviceParser()
    {
        $parser = self::createMock('Sinergi\BrowserDetector\Device');

        return $parser;
    }

    public function testGetName()
    {
        $provider = new SinergiBrowserDetector();

        $this->assertEquals('SinergiBrowserDetector', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new SinergiBrowserDetector();

        $this->assertEquals('https://github.com/sinergi/php-browser-detector', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new SinergiBrowserDetector();

        $this->assertEquals('sinergi/browser-detector', $provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new SinergiBrowserDetector();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new SinergiBrowserDetector();

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new SinergiBrowserDetector();

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
                'brand'    => false,
                'type'     => false,
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
        $provider = new SinergiBrowserDetector();

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'unknown');
        $this->assertIsRealResult($provider, true, 'unknown something');
        $this->assertIsRealResult($provider, true, 'something unknown');

        /*
         * device model
         */
        $this->assertIsRealResult($provider, false, 'Windows Phone', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Windows Phone something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Windows Phone', 'device', 'model');
    }

    public function testProvider()
    {
        $provider = new SinergiBrowserDetector();

        $this->assertInstanceOf('Sinergi\BrowserDetector\Browser', $provider->getBrowserParser(''));
        $this->assertInstanceOf('Sinergi\BrowserDetector\Os', $provider->getOperatingSystemParser(''));
        $this->assertInstanceOf('Sinergi\BrowserDetector\Device', $provider->getDeviceParser(''));
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $provider = new SinergiBrowserDetector();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('browserParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getBrowserParser());

        $property = $reflection->getProperty('osParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getDeviceParser());

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultValue()
    {
        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(false));
        $browserParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('unknown'));

        $provider = new SinergiBrowserDetector();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('browserParser');
        $property->setAccessible(true);
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getDeviceParser());

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultValue2()
    {
        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(false));
        $deviceParser = $this->getDeviceParser();
        $deviceParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Windows Phone'));

        $provider = new SinergiBrowserDetector();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('browserParser');
        $property->setAccessible(true);
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setAccessible(true);
        $property->setValue($provider, $deviceParser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(true));

        $provider = new SinergiBrowserDetector();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('browserParser');
        $property->setAccessible(true);
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getDeviceParser());

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
        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(false));
        $browserParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Chrome'));
        $browserParser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('28.0.1468'));

        $provider = new SinergiBrowserDetector();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('browserParser');
        $property->setAccessible(true);
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getDeviceParser());

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Chrome',
                'version' => [
                    'major' => 28,
                    'minor' => 0,
                    'patch' => 1468,

                    'alias' => null,

                    'complete' => '28.0.1468',
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
        $osParser = $this->getOsParser();
        $osParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Windows'));
        $osParser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('7.0.1'));

        $provider = new SinergiBrowserDetector();

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('browserParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getBrowserParser());

        $property = $reflection->getProperty('osParser');
        $property->setAccessible(true);
        $property->setValue($provider, $osParser);

        $property = $reflection->getProperty('deviceParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getDeviceParser());

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
        $osParser = $this->getOsParser();
        $osParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(\Sinergi\BrowserDetector\Browser::UNKNOWN));
        $osParser->expects($this->any())
            ->method('isMobile')
            ->will($this->returnValue(true));
        $deviceParser = $this->getDeviceParser();
        $deviceParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('iPad'));

        $provider = new SinergiBrowserDetector();

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('browserParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getBrowserParser());

        $property = $reflection->getProperty('osParser');
        $property->setAccessible(true);
        $property->setValue($provider, $osParser);

        $property = $reflection->getProperty('deviceParser');
        $property->setAccessible(true);
        $property->setValue($provider, $deviceParser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPad',
                'brand' => null,
                'type'  => null,

                'isMobile' => true,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device - name default
     */
    public function testParseDeviceDefaultValue()
    {
        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(false));
        $browserParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Chrome'));

        $deviceParser = $this->getDeviceParser();
        $deviceParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Windows Phone'));

        $provider = new SinergiBrowserDetector();

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('browserParser');
        $property->setAccessible(true);
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setAccessible(true);
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setAccessible(true);
        $property->setValue($provider, $deviceParser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Chrome',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],

            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => null,

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
