<?php

namespace UserAgentParserTest\Integration\Provider;

use ReflectionClass;
use UserAgentParser\Provider\BrowscapFull;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @coversNothing
 *
 * @internal
 */
class BrowscapFullTest extends AbstractBrowscapTestCase
{
    public function testMethodParse()
    {
        $provider = new BrowscapFull($this->getParserWithWarmCache('full'));
        $parser = $provider->getParser();

        // test method exists
        $class = new ReflectionClass($parser);
        $this->assertTrue($class->hasMethod('getBrowser'), 'method getBrowser() does not exist anymore');
        // test paramters
        $method = $class->getMethod('getBrowser');
        $parameters = $method->getParameters();
        $this->assertEquals(1, \count($parameters));
    }

    public function testMethodsResult()
    {
        $provider = new BrowscapFull($this->getParserWithWarmCache('full'));
        $parser = $provider->getParser();

        // @var $result \stdClass
        $result = $parser->getBrowser('A real user agent...');

        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('crawler', $result);
        $this->assertObjectHasAttribute('issyndicationreader', $result);

        $this->assertObjectHasAttribute('browser', $result);
        $this->assertObjectHasAttribute('browser_type', $result);
        $this->assertObjectHasAttribute('version', $result);

        $this->assertObjectHasAttribute('renderingengine_name', $result);
        $this->assertObjectHasAttribute('renderingengine_version', $result);

        $this->assertObjectHasAttribute('platform', $result);
        $this->assertObjectHasAttribute('platform_version', $result);

        $this->assertObjectHasAttribute('device_name', $result);
        $this->assertObjectHasAttribute('device_brand_name', $result);
        $this->assertObjectHasAttribute('device_type', $result);
        $this->assertObjectHasAttribute('ismobiledevice', $result);
        $this->assertObjectHasAttribute('device_pointing_method', $result);
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundWithWarmCache()
    {
        $provider = new BrowscapFull($this->getParserWithWarmCache('full'));

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new BrowscapFull($this->getParserWithWarmCache('full'));

        $result = $provider->parse('Mozilla/2.0 (compatible; Ask Jeeves)');
        $this->assertEquals([
            'browser' => [
                'name' => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'renderingEngine' => [
                'name' => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'operatingSystem' => [
                'name' => null,
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
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
            'bot' => [
                'isBot' => true,
                'name' => 'AskJeeves',
                'type' => 'Bot/Crawler',
            ],
        ], $result->toArray());

        // Test the raw result
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf('stdClass', $rawResult);
        $this->assertCount(50, (array) $rawResult);

        $this->assertObjectHasAttribute('browser_name_regex', $rawResult);
        $this->assertObjectHasAttribute('parent', $rawResult);
        $this->assertObjectHasAttribute('browser', $rawResult);
        $this->assertObjectHasAttribute('crawler', $rawResult);
    }

    public function testRealResultDevice()
    {
        $provider = new BrowscapFull($this->getParserWithWarmCache('full'));

        $result = $provider->parse('Mozilla/5.0 (SMART-TV; X11; Linux armv7l) AppleWebkit/537.42 (KHTML, like Gecko) Chromium/48.0.1349.2 Chrome/25.0.1349.2 Safari/537.42');
        $this->assertEquals([
            'browser' => [
                'name' => 'Chromium',
                'version' => [
                    'major' => 48,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '48.0',
                ],
            ],
            'renderingEngine' => [
                'name' => 'Blink',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'operatingSystem' => [
                'name' => 'Linux',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'device' => [
                'model' => 'Smart TV',
                'brand' => 'Samsung',
                'type' => 'TV Device',

                'isMobile' => null,
                'isTouch' => null,
            ],
            'bot' => [
                'isBot' => null,
                'name' => null,
                'type' => null,
            ],
        ], $result->toArray());

        // Test the raw result
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf('stdClass', $rawResult);
        $this->assertCount(50, (array) $rawResult);
    }
}
