<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\SinergiBrowserDetector;

/**
 * @covers UserAgentParser\Provider\SinergiBrowserDetector
 */
class SinergiBrowserDetectorTest extends AbstractProviderTestCase
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getBrowserParser()
    {
        $parser = $this->getMock('Sinergi\BrowserDetector\Browser', [], [], '', false);

        return $parser;
    }

    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getOsParser()
    {
        $parser = $this->getMock('Sinergi\BrowserDetector\Os', [], [], '', false);

        return $parser;
    }

    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDeviceParser()
    {
        $parser = $this->getMock('Sinergi\BrowserDetector\Device', [], [], '', false);

        return $parser;
    }

    public function testName()
    {
        $provider = new SinergiBrowserDetector();

        $this->assertEquals('SinergiBrowserDetector', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new SinergiBrowserDetector();

        $this->assertEquals('sinergi/browser-detector', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new SinergiBrowserDetector();

        $this->assertInternalType('string', $provider->getVersion());
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
    public function testNoResultFoundException()
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
                    'major'    => 28,
                    'minor'    => 0,
                    'patch'    => 1468,
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
                    'major'    => 7,
                    'minor'    => 0,
                    'patch'    => 1,
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
}
