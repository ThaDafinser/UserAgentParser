<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\YzalisUAParser;

/**
 * @covers UserAgentParser\Provider\YzalisUAParser
 */
class YzalisUAParserTest extends AbstractProviderTestCase
{
    /**
     *
     * @return \UAParser\Result\Result
     */
    private function getResultMock()
    {
        $result = $this->getMock('UAParser\Result\Result');

        $result->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue(new \UAParser\Result\BrowserResult()));

        $result->expects($this->any())
            ->method('getOperatingSystem')
            ->will($this->returnValue(new \UAParser\Result\OperatingSystemResult()));

        $result->expects($this->any())
            ->method('getDevice')
            ->will($this->returnValue(new \UAParser\Result\DeviceResult()));

        $result->expects($this->any())
            ->method('getEmailClient')
            ->will($this->returnValue(new \UAParser\Result\EmailClientResult()));

        $result->expects($this->any())
            ->method('getRenderingEngine')
            ->will($this->returnValue(new \UAParser\Result\RenderingEngineResult()));

        return $result;
    }

    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser($returnValue = null)
    {
        $parser = $this->getMock('UAParser\UAParser', [], [], '', false);
        $parser->expects($this->any())
            ->method('parse')
            ->will($this->returnValue($returnValue));

        return $parser;
    }

    public function testName()
    {
        $provider = new YzalisUAParser();

        $this->assertEquals('YzalisUAParser', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new YzalisUAParser();

        $this->assertEquals('yzalis/ua-parser', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new YzalisUAParser();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testDetectionCapabilities()
    {
        $provider = new YzalisUAParser();

        $this->assertEquals([

        'browser' => [
            'name'    => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name'    => true,
            'version' => true,
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
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => false,
            'name'  => false,
            'type'  => false,
        ],
    ], $provider->getDetectionCapabilities());
    }

    public function testParser()
    {
        $parser = $this->getParser();

        $provider = new YzalisUAParser();
        $provider->setParser($parser);

        $this->assertSame($parser, $provider->getParser());

        $provider->setParser(null);
        $this->assertInstanceOf('UAParser\UAParser', $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $parser = $this->getParser($this->getResultMock());

        $provider = new YzalisUAParser();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundExceptionDefaultValue()
    {
        $result = $this->getResultMock();
        $result->getBrowser()->fromArray([
            'family' => 'Other',
        ]);
        $parser = $this->getParser($result);

        $provider = new YzalisUAParser();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Browser small
     */
    public function testParseBrowser()
    {
        $result = $this->getResultMock();
        $result->getBrowser()->fromArray([
            'family' => 'Firefox',
            'major'  => 3,
            'minor'  => 2,
            'patch'  => 1,
        ]);
        $result->getRenderingEngine()->fromArray([
            'family'  => 'WebKit',
            'version' => '6.5.4',
        ]);

        $parser = $this->getParser($result);

        $provider = new YzalisUAParser();
        $provider->setParser($parser);

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

            'renderingEngine' => [
                'name'    => 'WebKit',
                'version' => [
                    'major' => 6,
                    'minor' => 5,
                    'patch' => 4,

                    'alias' => null,

                    'complete' => '6.5.4',
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
        $result = $this->getResultMock();
        $result->getOperatingSystem()->fromArray([
            'family' => 'Windows',
            'major'  => 7,
            'minor'  => 0,
            'patch'  => 1,
        ]);

        $parser = $this->getParser($result);

        $provider = new YzalisUAParser();
        $provider->setParser($parser);

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
        $result = $this->getResultMock();
        $result->getDevice()->fromArray([
            'constructor' => 'Apple',
            'model'       => 'iPad',
            'type'        => 'tablet',
        ]);

        $parser = $this->getParser($result);

        $provider = new YzalisUAParser();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPad',
                'brand' => 'Apple',
                'type'  => 'tablet',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only model
     */
    public function testParseDeviceOnlyModel()
    {
        $result = $this->getResultMock();
        $result->getDevice()->fromArray([
            'model' => 'iPad',
            'type'  => 'mobile',
        ]);

        $parser = $this->getParser($result);

        $provider = new YzalisUAParser();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPad',
                'brand' => null,
                'type'  => 'mobile',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
