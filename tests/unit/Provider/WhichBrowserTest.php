<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\WhichBrowser;

/**
 * @covers UserAgentParser\Provider\WhichBrowser
 */
class WhichBrowserTest extends AbstractProviderTestCase
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser()
    {
        $parser = $this->getMock('WhichBrowser\Parser', [], [], '', false);

        $parser->browser = new \WhichBrowser\Model\Browser();
        $parser->engine  = new \WhichBrowser\Model\Engine();
        $parser->os      = new \WhichBrowser\Model\Os();
        $parser->device  = new \WhichBrowser\Model\Device();

        return $parser;
    }

    public function testName()
    {
        $provider = new WhichBrowser();

        $this->assertEquals('WhichBrowser', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new WhichBrowser();

        $this->assertEquals('whichbrowser/parser', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new WhichBrowser();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testParser()
    {
        $provider = new WhichBrowser();

        $this->assertInstanceOf('WhichBrowser\Parser', $provider->getParser([]));
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $parser = $this->getParser();

        $provider = new WhichBrowser();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('bot'));
        $parser->browser = new \WhichBrowser\Model\Browser([
            'name' => 'Googlebot',
        ]);

        $provider = new WhichBrowser();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

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
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->browser = new \WhichBrowser\Model\Browser([
            'name'    => 'Firefox',
            'version' => new \WhichBrowser\Model\Version([
                'value' => '3.2.1',
            ]),
        ]);

        $provider = new WhichBrowser();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Firefox',
                'version' => [
                    'major'    => 3,
                    'minor'    => 2,
                    'patch'    => 1,

                    'alias' => null,

                    'complete' => '3.2.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only "using"
     */
    public function testParseBrowserUsing()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));

        $using = new \WhichBrowser\Model\Using([
            'name'    => 'Another',
            'version' => new \WhichBrowser\Model\Version([
                'value' => '4.7.3',
            ]),
        ]);

        $parser->browser = new \WhichBrowser\Model\Browser([
            'using'    => $using,
        ]);

        $provider = new WhichBrowser();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Another',
                'version' => [
                    'major'    => 4,
                    'minor'    => 7,
                    'patch'    => 3,

                    'alias' => null,

                    'complete' => '4.7.3',
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
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->engine = new \WhichBrowser\Model\Engine([
            'name'    => 'Webkit',
            'version' => new \WhichBrowser\Model\Version([
                'value' => '3.2.1',
            ]),
        ]);

        $provider = new WhichBrowser();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'renderingEngine' => [
                'name'    => 'Webkit',
                'version' => [
                    'major'    => 3,
                    'minor'    => 2,
                    'patch'    => 1,

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
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->os = new \WhichBrowser\Model\Os([
            'name'    => 'Windows',
            'version' => new \WhichBrowser\Model\Version([
                'value' => '7.0.1',
            ]),
        ]);

        $provider = new WhichBrowser();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

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
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));
        $parser->expects($this->any())
        ->method('getType')
        ->will($this->returnValue('watch'));

        $parser->device = new \WhichBrowser\Model\Device([
            'identified'   => true,
            'model'        => 'iPhone',
            'manufacturer' => 'Apple',
            'type'         => 'watch',
        ]);

        $parser->expects($this->any())
        ->method('isType')
        ->will($this->returnValue(true));

        $provider = new WhichBrowser();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => 'watch',

                'isMobile' => true,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
