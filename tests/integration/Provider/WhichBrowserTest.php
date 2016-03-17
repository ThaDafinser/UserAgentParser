<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\WhichBrowser;

/**
 * @coversNothing
 */
class WhichBrowserTest extends AbstractProviderTestCase
{
    public function testRealResult()
    {
        $provider = new WhichBrowser();

        $parser = $provider->getParser([
            'User-Agent' => 'A real user agent...',
        ]);

        $this->assertInstanceOf('WhichBrowser\Parser', $parser);

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('isDetected'), 'method isDetected() does not exist anymore');
        $this->assertTrue($class->hasMethod('toArray'), 'method toArray() does not exist anymore');
        $this->assertTrue($class->hasMethod('getType'), 'method getType() does not exist anymore');
        $this->assertTrue($class->hasMethod('isType'), 'method isType() does not exist anymore');

        $this->assertTrue($class->hasProperty('browser'), 'property browser does not exist anymore');
        $this->assertInstanceOf('WhichBrowser\Model\Browser', $parser->browser);

        $this->assertTrue($class->hasProperty('engine'), 'property engine does not exist anymore');
        $this->assertInstanceOf('WhichBrowser\Model\Engine', $parser->engine);

        $this->assertTrue($class->hasProperty('os'), 'property os does not exist anymore');
        $this->assertInstanceOf('WhichBrowser\Model\Os', $parser->os);

        $this->assertTrue($class->hasProperty('device'), 'property device does not exist anymore');
        $this->assertInstanceOf('WhichBrowser\Model\Device', $parser->device);
    }

    public function testClassBrowserResult()
    {
        $class = new \ReflectionClass('WhichBrowser\Model\Browser');

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersion'), 'method getVersion() does not exist anymore');

        $this->assertTrue($class->hasProperty('using'), 'property using does not exist anymore');
    }

    public function testClassBrowserUsingResult()
    {
        $class = new \ReflectionClass('WhichBrowser\Model\Using');

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersion'), 'method getVersion() does not exist anymore');
    }

    public function testClassEngineResult()
    {
        $class = new \ReflectionClass('WhichBrowser\Model\Engine');

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersion'), 'method getVersion() does not exist anymore');
    }

    public function testClassOsResult()
    {
        $class = new \ReflectionClass('WhichBrowser\Model\Os');

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersion'), 'method getVersion() does not exist anymore');
    }

    public function testClassDeviceResult()
    {
        $class = new \ReflectionClass('WhichBrowser\Model\Device');

        $this->assertTrue($class->hasMethod('getModel'), 'method getModel() does not exist anymore');
        $this->assertTrue($class->hasMethod('getManufacturer'), 'method getManufacturer() does not exist anymore');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new WhichBrowser();

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new WhichBrowser();

        $result = $provider->parse('Googlebot/2.1 (+http://www.google.com/bot.html)');
        $this->assertEquals([
            'browser' => [
                'name'    => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'renderingEngine' => [
                'name'    => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
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
            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => null,

                'isMobile' => null,
                'isTouch'  => null,
            ],
            'bot' => [
                'isBot' => true,
                'name'  => 'Googlebot',
                'type'  => null,
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();
        $this->assertEquals([
            'browser'   => [
                'name'    => 'Googlebot',
                'version' => '2.1',
            ],
            'device'    => [
                'type' => 'bot',
            ],
        ], $rawResult);
    }

    public function testRealResultDevice()
    {
        $provider = new WhichBrowser();

        $result = $provider->parse('Mozilla/5.0 (Linux; Android 4.3; SCH-R970C Build/JSS15J) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.114 Mobile Safari/537.3');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Chrome',
                'version' => [
                    'major' => 34,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '34',
                ],
            ],
            'renderingEngine' => [
                'name'    => 'Blink',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'operatingSystem' => [
                'name'    => 'Android',
                'version' => [
                    'major' => 4,
                    'minor' => 3,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '4.3',
                ],
            ],
            'device' => [
                'model' => 'Galaxy S4',
                'brand' => 'Samsung',
                'type'  => 'mobile:smart',

                'isMobile' => true,
                'isTouch'  => null,
            ],
            'bot' => [
                'isBot' => null,
                'name'  => null,
                'type'  => null,
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();
        $this->assertEquals([
            'browser'   => [
                'name'    => 'Chrome',
                'version' => '34',
                'type'    => 'browser',
            ],
            'engine' => [
                'name'    => 'Blink',
            ],
            'os' => [
                'name'    => 'Android',
                'version' => '4.3',
            ],
            'device'    => [
                'type'         => 'mobile',
                'subtype'      => 'smart',
                'manufacturer' => 'Samsung',
                'model'        => 'Galaxy S4',
            ],
        ], $rawResult);
    }
}
