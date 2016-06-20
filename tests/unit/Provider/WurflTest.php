<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\Wurfl;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *         
 *          @covers UserAgentParser\Provider\Wurfl
 */
class WurflTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    private function getManager()
    {
        $mock = self::createMock('Wurfl\Manager');

        return $mock;
    }

    public function testGetName()
    {
        $provider = new Wurfl($this->getManager());

        $this->assertEquals('Wurfl', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new Wurfl($this->getManager());

        $this->assertEquals('https://github.com/mimmi20/Wurfl', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new Wurfl($this->getManager());

        $this->assertEquals('mimmi20/wurfl', $provider->getPackageName());
    }

    public function testVersionOld()
    {
        $return          = new \stdClass();
        $return->version = 'for API 1.6.4, db.scientiamobile.com - 2015-12-03 14:33:12';

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getWurflInfo')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $this->assertInternalType('string', $provider->getVersion());
        $this->assertEquals('1.6.4', $provider->getVersion());
    }

    public function testVersion()
    {
        $return          = new \stdClass();
        $return->version = 'API 1.7.0 - data.scientiamobile.com - 2016-01-28 00:30:29';

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getWurflInfo')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $this->assertInternalType('string', $provider->getVersion());
        $this->assertEquals('1.7.0', $provider->getVersion());
    }

    public function testVersionNull()
    {
        $return          = new \stdClass();
        $return->version = 'something elese';

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getWurflInfo')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $this->assertNull($provider->getVersion());
    }

    public function testUpdateDate()
    {
        $return              = new \stdClass();
        $return->lastUpdated = '2015-10-16 11:09:44 -0400';

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getWurflInfo')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testUpdateDateBlank()
    {
        $return              = new \stdClass();
        $return->lastUpdated = '';

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getWurflInfo')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $this->assertNull($provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new Wurfl($this->getManager());

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
                'type'     => true,
                'isMobile' => true,
                'isTouch'  => true,
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
        $manager = $this->getManager();

        $provider = new Wurfl($manager);

        /*
         * OS name
         */
        $this->assertIsRealResult($provider, false, 'unknown', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'unknown something', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'something unknown', 'operatingSystem', 'name');

        $this->assertIsRealResult($provider, false, 'en', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'en something', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'something en', 'operatingSystem', 'name');

        $this->assertIsRealResult($provider, false, 'en_US', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'en_US something', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'something en_US', 'operatingSystem', 'name');

        $this->assertIsRealResult($provider, false, 'Desktop', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'Desktop something', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'something Desktop', 'operatingSystem', 'name');

        $this->assertIsRealResult($provider, false, 'Mobile', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'Mobile something', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'something Mobile', 'operatingSystem', 'name');

        $this->assertIsRealResult($provider, false, 'Randomized by FreeSafeIP.com', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'Randomized by FreeSafeIP.com something', 'operatingSystem', 'name');
        $this->assertIsRealResult($provider, true, 'something Randomized by FreeSafeIP.com', 'operatingSystem', 'name');

        /*
         * Device brand
         */
        $this->assertIsRealResult($provider, false, 'Generic', 'device', 'brand');
        $this->assertIsRealResult($provider, true, 'Generic something', 'device', 'brand');
        $this->assertIsRealResult($provider, true, 'something Generic', 'device', 'brand');

        /*
         * Device model
         */
        $this->assertIsRealResult($provider, false, 'Android', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Android 5.0', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Android', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Windows Phone', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Windows Phone something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Windows Phone', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Windows Mobile', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Windows Mobile something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Windows Mobile', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Firefox', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Firefox something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Firefox', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'unrecognized', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'unrecognized something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something unrecognized', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Generic', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Generic something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Generic', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Disguised as Macintosh', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Disguised as Macintosh something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Disguised as Macintosh', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Windows RT', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'Windows RT something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Windows RT', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Tablet on Android', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Tablet on Android something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Tablet on Android', 'device', 'model');
    }

    public function testParser()
    {
        $manager = $this->getManager();

        $provider = new Wurfl($manager);

        $this->assertSame($manager, $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $return = self::createMock('Wurfl\CustomDevice');

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getDeviceForUserAgent')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $return     = self::createMock('Wurfl\CustomDevice');
        $return->id = 'some_id';
        $return->expects($this->any())
            ->method('getVirtualCapability')
            ->with('is_robot')
            ->will($this->returnValue('true'));

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getDeviceForUserAgent')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

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
        $return     = self::createMock('Wurfl\CustomDevice');
        $return->id = 'some_id';

        $map = [
            [
                'is_robot',
                'false',
            ],
            [
                'advertised_browser',
                'Firefox',
            ],
            [
                'advertised_browser_version',
                '3.0.1',
            ],
        ];

        $return->expects($this->any())
            ->method('getVirtualCapability')
            ->will($this->returnValueMap($map));

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getDeviceForUserAgent')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 0,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.0.1',
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
        $return     = self::createMock('Wurfl\CustomDevice');
        $return->id = 'some_id';

        $map = [
            [
                'is_robot',
                'false',
            ],
            [
                'advertised_device_os',
                'Windows',
            ],
            [
                'advertised_device_os_version',
                '7.0.1',
            ],
        ];

        $return->expects($this->any())
            ->method('getVirtualCapability')
            ->will($this->returnValueMap($map));

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getDeviceForUserAgent')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

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
     * OS - default value
     */
    public function testParseOperatingSystemDefaultValue()
    {
        $return     = self::createMock('Wurfl\CustomDevice');
        $return->id = 'some_id';

        $map = [
            [
                'is_robot',
                'false',
            ],
            [
                'advertised_device_os',
                'Unknown',
            ],
        ];

        $return->expects($this->any())
            ->method('getVirtualCapability')
            ->will($this->returnValueMap($map));

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getDeviceForUserAgent')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => null,
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
     * Device only
     */
    public function testParseDevice()
    {
        $return     = self::createMock('Wurfl\CustomDevice');
        $return->id = 'some_id';

        $map = [
            [
                'is_robot',
                'false',
            ],
            [
                'is_full_desktop',
                'false',
            ],

            [
                'is_mobile',
                'true',
            ],
            [
                'is_touchscreen',
                'true',
            ],
            [
                'form_factor',
                'smartphone',
            ],
        ];

        $return->expects($this->any())
            ->method('getVirtualCapability')
            ->will($this->returnValueMap($map));

        $map = [
            [
                'model_name',
                'iPhone',
            ],
            [
                'brand_name',
                'Apple',
            ],
        ];
        $return->expects($this->any())
            ->method('getCapability')
            ->will($this->returnValueMap($map));

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getDeviceForUserAgent')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

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

    /**
     * Device only
     */
    public function testParseDeviceDefaultValue()
    {
        $return     = self::createMock('Wurfl\CustomDevice');
        $return->id = 'some_id';

        $map = [
            [
                'is_robot',
                'false',
            ],
            [
                'is_full_desktop',
                'false',
            ],

            [
                'form_factor',
                'smartphone',
            ],
        ];

        $return->expects($this->any())
            ->method('getVirtualCapability')
            ->will($this->returnValueMap($map));

        $map = [
            [
                'model_name',
                'Android',
            ],
            [
                'brand_name',
                'Generic',
            ],
        ];
        $return->expects($this->any())
            ->method('getCapability')
            ->will($this->returnValueMap($map));

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getDeviceForUserAgent')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'smartphone',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
