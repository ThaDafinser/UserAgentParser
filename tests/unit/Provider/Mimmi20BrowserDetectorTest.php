<?php
namespace UserAgentParserTest\Unit\Provider;

use UaResult\Result\Result;
use UserAgentParser\Provider\Mimmi20BrowserDetector;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers UserAgentParser\Provider\Mimmi20BrowserDetector
 */
class Mimmi20BrowserDetectorTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser($returnValue = null)
    {
        if ($returnValue === null) {
            $type = self::createMock('UaBrowserType\Type');
            $type->expects($this->any())
                ->method('getType')
                ->will($this->returnValue(''));

            $browser = self::createMock('UaResult\Browser\Browser');
            $browser->expects($this->any())
                ->method('getType')
                ->will($this->returnValue($type));

            $returnValue = self::createMock('UaResult\Result\Result');
            $returnValue->expects($this->any())
                ->method('getBrowser')
                ->will($this->returnValue($browser));
        }

        $parser = self::createMock('BrowserDetector\Detector');
        $parser->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($returnValue));

        return $parser;
    }

    private function getBrowserMock($typeString = 'browser')
    {
        $version = self::createMock('BrowserDetector\Version\Version');
        $version->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue(null));

        $type = self::createMock('UaBrowserType\Type');
        $type->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($typeString));

        $browser = self::createMock('UaResult\Browser\Browser');
        $browser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(null));
        $browser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue($version));
        $browser->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        return $browser;
    }

    private function getEngineMock()
    {
        $version = self::createMock('BrowserDetector\Version\Version');
        $version->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue(null));

        $parser = self::createMock('UaResult\Engine\Engine');
        $parser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(null));
        $parser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue($version));

        return $parser;
    }

    private function getOsMock()
    {
        $version = self::createMock('BrowserDetector\Version\Version');
        $version->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue(null));

        $parser = self::createMock('UaResult\Os\Os');
        $parser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(null));
        $parser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue($version));

        return $parser;
    }

    private function getDeviceMock()
    {
        $company = self::createMock('UaResult\Company\Company');
        $company->expects($this->any())
            ->method('getBrandName')
            ->will($this->returnValue(null));

        $type = self::createMock('UaBrowserType\Type');
        $type->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(null));

        $parser = self::createMock('UaResult\Device\Device');
        $parser->expects($this->any())
            ->method('getDeviceName')
            ->will($this->returnValue(null));
        $parser->expects($this->any())
            ->method('getBrand')
            ->will($this->returnValue($company));
        $parser->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        return $parser;
    }

    public function testGetName()
    {
        $provider = new Mimmi20BrowserDetector();

        $this->assertEquals('Mimmi20BrowserDetector', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new Mimmi20BrowserDetector();

        $this->assertEquals('https://github.com/mimmi20/BrowserDetector', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new Mimmi20BrowserDetector();

        $this->assertEquals('mimmi20/browser-detector', $provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new Mimmi20BrowserDetector();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new Mimmi20BrowserDetector();

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new Mimmi20BrowserDetector();

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
                'isMobile' => false,
                'isTouch'  => true,
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
        $provider = new Mimmi20BrowserDetector();

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'unknown');
        $this->assertIsRealResult($provider, true, 'unknown something');
        $this->assertIsRealResult($provider, true, 'something unknown');

        $this->assertIsRealResult($provider, false, 'Unknown');
        $this->assertIsRealResult($provider, true, 'Unknown something');
        $this->assertIsRealResult($provider, true, 'something Unknown');
    }

    public function testParser()
    {
        $provider = new Mimmi20BrowserDetector();

        $instance = $provider->getParser();
        $this->assertInstanceOf('BrowserDetector\Detector', $instance);

        // singleton test
        $this->assertEquals($instance, $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $parser = $this->getParser();

        $provider = new Mimmi20BrowserDetector($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function testProviderNameAndVersionIsInResult()
    {
        $type = self::createMock('UaBrowserType\Type');
        $type->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('bot'));

        $browser = self::createMock('UaResult\Browser\Browser');
        $browser->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        $returnValue = self::createMock('UaResult\Result\Result');
        $returnValue->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($browser));

        $parser = $this->getParser($returnValue);

        $provider = new Mimmi20BrowserDetector($parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('Mimmi20BrowserDetector', $result->getProviderName());
        $this->assertRegExp('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $type = self::createMock('UaBrowserType\Type');
        $type->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('bot'));

        $browser = self::createMock('UaResult\Browser\Browser');
        $browser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Googlebot'));
        $browser->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        $returnValue = self::createMock('UaResult\Result\Result');
        $returnValue->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($browser));

        $parser = $this->getParser($returnValue);

        $provider = new Mimmi20BrowserDetector($parser);

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
        $version = self::createMock('BrowserDetector\Version\Version');
        $version->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('3.2.1'));

        $type = self::createMock('UaBrowserType\Type');
        $type->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('browser'));

        $browser = self::createMock('UaResult\Browser\Browser');
        $browser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Firefox'));
        $browser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue($version));
        $browser->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        $returnValue = self::createMock('UaResult\Result\Result');
        $returnValue->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($browser));
        $returnValue->expects($this->any())
            ->method('getEngine')
            ->will($this->returnValue($this->getEngineMock()));
        $returnValue->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue($this->getOsMock()));
        $returnValue->expects($this->any())
            ->method('getDevice')
            ->will($this->returnValue($this->getDeviceMock()));

        $parser = $this->getParser($returnValue);

        $provider = new Mimmi20BrowserDetector($parser);

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
     * Rendering engine only
     */
    public function testParseRenderingEngine()
    {
        $version = self::createMock('BrowserDetector\Version\Version');
        $version->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('3.2.1'));

        $engine = self::createMock('UaResult\Engine\Engine');
        $engine->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Webkit'));
        $engine->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue($version));

        $returnValue = self::createMock('UaResult\Result\Result');
        $returnValue->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($this->getBrowserMock()));
        $returnValue->expects($this->any())
            ->method('getEngine')
            ->will($this->returnValue($engine));
        $returnValue->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue($this->getOsMock()));
        $returnValue->expects($this->any())
            ->method('getDevice')
            ->will($this->returnValue($this->getDeviceMock()));

        $parser = $this->getParser($returnValue);

        $provider = new Mimmi20BrowserDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'renderingEngine' => [
                'name'    => 'Webkit',
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
        $version = self::createMock('BrowserDetector\Version\Version');
        $version->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('3.2.1'));

        $os = self::createMock('UaResult\Os\Os');
        $os->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Windows'));
        $os->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue($version));

        $returnValue = self::createMock('UaResult\Result\Result');
        $returnValue->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($this->getBrowserMock()));
        $returnValue->expects($this->any())
            ->method('getEngine')
            ->will($this->returnValue($this->getEngineMock()));
        $returnValue->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue($os));
        $returnValue->expects($this->any())
            ->method('getDevice')
            ->will($this->returnValue($this->getDeviceMock()));

        $parser = $this->getParser($returnValue);

        $provider = new Mimmi20BrowserDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'Windows',
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
     * Device only
     */
    public function testParseDeviceMobilephone()
    {
        $brand = self::createMock('UaResult\Company\Company');
        $brand->expects($this->any())
            ->method('getBrandName')
            ->will($this->returnValue('Apple'));
        $type = self::createMock('UaDeviceType\Type');
        $type->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('mobilephone'));

        $device = self::createMock('UaResult\Device\Device');
        $device->expects($this->any())
            ->method('getDeviceName')
            ->will($this->returnValue('iPhone 7'));
        $device->expects($this->any())
            ->method('getBrand')
            ->will($this->returnValue($brand));
        $device->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));
        $device->expects($this->any())
            ->method('getPointingMethod')
            ->will($this->returnValue('touchscreen'));

        $returnValue = self::createMock('UaResult\Result\Result');
        $returnValue->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($this->getBrowserMock()));
        $returnValue->expects($this->any())
            ->method('getEngine')
            ->will($this->returnValue($this->getEngineMock()));
        $returnValue->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue($this->getOsMock()));
        $returnValue->expects($this->any())
            ->method('getDevice')
            ->will($this->returnValue($device));

        $parser = $this->getParser($returnValue);

        $provider = new Mimmi20BrowserDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone 7',
                'brand' => 'Apple',
                'type'  => 'mobilephone',

                'isMobile' => null,
                'isTouch'  => true,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
