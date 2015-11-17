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

        $parser->browser = new \WhichBrowser\Browser();
        $parser->engine  = new \WhichBrowser\Engine();
        $parser->os      = new \WhichBrowser\Os();
        $parser->device  = new \WhichBrowser\Device();

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
            ->method('isType')
            ->will($this->returnValue(true));
        $parser->browser = new \WhichBrowser\Browser([
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

        $parser->browser = new \WhichBrowser\Browser([
            'name'    => 'Firefox',
            'version' => new \WhichBrowser\Version([
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
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->engine = new \WhichBrowser\Engine([
            'name'    => 'Webkit',
            'version' => new \WhichBrowser\Version([
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

        $parser->os = new \WhichBrowser\Os([
            'name'    => 'Windows',
            'version' => new \WhichBrowser\Version([
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

        $parser->device = new \WhichBrowser\Device([
            'identified'   => true,
            'model'        => 'iPhone',
            'manufacturer' => 'Apple',
            'type'         => 'smartphone',
        ]);

        $parser->expects($this->at(2))
            ->method('isType')
            ->with('bot')
            ->will($this->returnValue(false));

        $parser->expects($this->at(3))
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
                'type'  => 'smartphone',

                'isMobile' => true,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
