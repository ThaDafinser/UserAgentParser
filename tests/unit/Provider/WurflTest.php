<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\Wurfl;

/**
 * @covers UserAgentParser\Provider\Wurfl
 */
class WurflTest extends AbstractProviderTestCase
{
    private function getManager()
    {
        $mock = $this->getMock('Wurfl\Manager', [], [], '', false);

        return $mock;
    }

    public function testName()
    {
        $provider = new Wurfl($this->getManager());

        $this->assertEquals('Wurfl', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new Wurfl($this->getManager());

        $this->assertEquals('mimmi20/wurfl', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $return          = new \stdClass();
        $return->version = 'test - 2015-12-04 00:00:00';

        $manager = $this->getManager();
        $manager->expects($this->any())
            ->method('getWurflInfo')
            ->will($this->returnValue($return));

        $provider = new Wurfl($manager);

        $this->assertInternalType('string', $provider->getVersion());
        $this->assertEquals('2015-12-04 00:00:00', $provider->getVersion());
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

    public function testParser()
    {
        $manager = $this->getManager();

        $provider = new Wurfl($manager);

        $this->assertSame($manager, $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $return = $this->getMock('Wurfl\CustomDevice', [], [], '', false);

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
        $return     = $this->getMock('Wurfl\CustomDevice', [], [], '', false);
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
        $return     = $this->getMock('Wurfl\CustomDevice', [], [], '', false);
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
                    'major'    => 3,
                    'minor'    => 0,
                    'patch'    => 1,

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
        $return     = $this->getMock('Wurfl\CustomDevice', [], [], '', false);
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
                    'major'    => 7,
                    'minor'    => 0,
                    'patch'    => 1,

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
        $return     = $this->getMock('Wurfl\CustomDevice', [], [], '', false);
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
     * Device no valid model
     */
    public function testParseDeviceNoValidModel()
    {
        $return     = $this->getMock('Wurfl\CustomDevice', [], [], '', false);
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
                'Android something',
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
                'model' => null,
                'brand' => 'Apple',
                'type'  => 'smartphone',

                'isMobile' => true,
                'isTouch'  => true,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
