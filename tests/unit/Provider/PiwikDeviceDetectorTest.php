<?php
namespace UserAgentParserTest\Provider;

use DeviceDetector\DeviceDetector;
use UserAgentParser\Provider\PiwikDeviceDetector;

/**
 * @covers UserAgentParser\Provider\PiwikDeviceDetector
 */
class PiwikDeviceDetectorTest extends AbstractProviderTestCase
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser()
    {
        $parser = $this->getMock('DeviceDetector\DeviceDetector');

        return $parser;
    }

    public function testName()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertEquals('PiwikDeviceDetector', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertEquals('piwik/device-detector', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testParser()
    {
        $parser = $this->getParser();

        $provider = new PiwikDeviceDetector();
        $provider->setParser($parser);

        $this->assertSame($parser, $provider->getParser());

        $provider->setParser(null);
        $this->assertInstanceOf('DeviceDetector\DeviceDetector', $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $parser = $this->getParser();

        $provider = new PiwikDeviceDetector();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isBot')
            ->will($this->returnValue(true));
        $parser->expects($this->any())
            ->method('getBot')
            ->will($this->returnValue([
            'name'     => 'Hatena RSS',
            'category' => 'something',
        ]));

        $provider = new PiwikDeviceDetector();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => 'Hatena RSS',
                'type'  => 'something',
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
        $parser->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue([
            'name'    => 'Firefox',
            'version' => '3.0',
            'engine'  => 'WebKit',
        ]));
        $parser->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue([]));

        $provider = new PiwikDeviceDetector();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Firefox',
                'version' => [
                    'major'    => 3,
                    'minor'    => 0,
                    'patch'    => null,
                    'complete' => '3.0',
                ],
            ],

            'renderingEngine' => [
                'name'    => 'WebKit',
                'version' => [
                    'major'    => null,
                    'minor'    => null,
                    'patch'    => null,
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
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue([
            'engine' => DeviceDetector::UNKNOWN,
        ]));
        $parser->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue([
            'name'    => 'Windows',
            'version' => '7.0',
        ]));

        $provider = new PiwikDeviceDetector();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'Windows',
                'version' => [
                    'major'    => 7,
                    'minor'    => 0,
                    'patch'    => null,
                    'complete' => '7.0',
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
            ->method('getClient')
            ->will($this->returnValue([]));
        $parser->expects($this->any())
        ->method('getOs')
        ->will($this->returnValue([]));

        $parser->expects($this->any())
            ->method('getDevice')
            ->will($this->returnValue(1));

        $parser->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue('iPhone'));
        $parser->expects($this->any())
            ->method('getBrandName')
            ->will($this->returnValue('Apple'));
        $parser->expects($this->any())
            ->method('getDeviceName')
            ->will($this->returnValue('smartphone'));

        $parser->expects($this->any())
            ->method('isMobile')
            ->will($this->returnValue(true));

        $parser->expects($this->any())
            ->method('isTouchEnabled')
            ->will($this->returnValue(true));

        $provider = new PiwikDeviceDetector();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => 'smartphone',

                'isMobile' => true,
                'isTouch'  => true,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
