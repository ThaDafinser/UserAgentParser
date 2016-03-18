<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\Wurfl;

/**
 * @coversNothing
 */
class WurflTest extends AbstractProviderTestCase
{
    private function getWurfl()
    {
        // config
        $wurflConfig = new \Wurfl\Configuration\InMemoryConfig();
        $wurflConfig->wurflFile('tests/resources/wurfl/wurfl.xml');
        $wurflConfig->persistence('memory');

        // Setup Caching
        $wurflConfig->cache('memory');

        // persistance
        $persistenceStorage = \Wurfl\Storage\Factory::create($wurflConfig->persistence);

        // cache
        $cacheStorage = \Wurfl\Storage\Factory::create($wurflConfig->cache);

        return new \Wurfl\Manager($wurflConfig, $persistenceStorage, $cacheStorage);
    }

    public function testMethodParse()
    {
        $provider = new Wurfl($this->getWurfl());
        $parser   = $provider->getParser();

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('getDeviceForUserAgent'), 'method getDeviceForUserAgent() does not exist anymore');

        /*
         * test paramters
         */
        $method     = $class->getMethod('getDeviceForUserAgent');
        $parameters = $method->getParameters();

        $this->assertEquals(1, count($parameters));
    }

    public function testMethodsResult()
    {
        $provider = new Wurfl($this->getWurfl());
        $parser   = $provider->getParser();

        /* @var $result \Wurfl\CustomDevice */
        $result = $parser->getDeviceForUserAgent('A real user agent...');

        $this->assertInstanceOf('Wurfl\CustomDevice', $result);

        /*
         * test method exists
         */
        $class = new \ReflectionClass($result);

        $this->assertTrue($class->hasMethod('getAllVirtualCapabilities'), 'method getAllVirtualCapabilities() does not exist anymore');
        $this->assertTrue($class->hasMethod('getAllCapabilities'), 'method getAllCapabilities() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVirtualCapability'), 'method getVirtualCapability() does not exist anymore');
        $this->assertTrue($class->hasMethod('getCapability'), 'method isDetected() does not exist anymore');

        // there is no method to get the id and it's no normal property
        $this->assertEquals('generic', $result->id);
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new Wurfl($this->getWurfl());

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new Wurfl($this->getWurfl());

        $result = $provider->parse('Googlebot-News');
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
                'name'  => null,
                'type'  => null,
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertInternalType('array', $rawResult);
        $this->assertCount(2, $rawResult);
        $this->assertArrayHasKey('virtual', $rawResult);
        $this->assertArrayHasKey('all', $rawResult);

        $virtual = $rawResult['virtual'];
        $this->assertCount(22, $virtual);
        $this->assertArrayHasKey('is_robot', $virtual);
        $this->assertArrayHasKey('is_smartphone', $virtual);
        $this->assertArrayHasKey('complete_device_name', $virtual);

        $all = $rawResult['all'];
        $this->assertCount(530, $all);
        $this->assertArrayHasKey('is_wireless_device', $all);
        $this->assertArrayHasKey('table_support', $all);
        $this->assertArrayHasKey('resolution_width', $all);
    }

    public function testRealResultDevice()
    {
        $provider = new Wurfl($this->getWurfl());

        $result = $provider->parse('Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A538a Safari/419.3');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Mobile Safari',
                'version' => [
                    'major' => 3,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '3.0',
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
                'name'    => 'iOS',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => 'Feature Phone',

                'isMobile' => true,
                'isTouch'  => true,
            ],
            'bot' => [
                'isBot' => null,
                'name'  => null,
                'type'  => null,
            ],
        ], $result->toArray());
    }
}
