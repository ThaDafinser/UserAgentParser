<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\BrowscapLite;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * 
 * @coversNothing
 */
class BrowscapLiteTest extends AbstractBrowscapTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundWithWarmCache()
    {
        $provider = new BrowscapLite($this->getParserWithWarmCache('lite'));

        $result = $provider->parse('...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\InvalidArgumentException
     * @expectedExceptionMessage You need to warm-up the cache first to use this provider
     */
    public function testColdCacheException()
    {
        $provider = new BrowscapLite($this->getParserWithColdCache('lite'));

        $result = $provider->parse('...');
    }

    public function testRealResultDevice()
    {
        $provider = new BrowscapLite($this->getParserWithWarmCache('lite'));

        $result = $provider->parse('Mozilla/5.0 (SMART-TV; X11; Linux armv7l) AppleWebkit/537.42 (KHTML, like Gecko) Chromium/48.0.1349.2 Chrome/25.0.1349.2 Safari/537.42');

        $this->assertEquals([
            'browser' => [
                'name'    => 'Chromium',
                'version' => [
                    'major' => 48,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '48.0',
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
                'name'    => 'Linux',
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
                'type'  => 'TV Device',

                'isMobile' => null,
                'isTouch'  => null,
            ],
            'bot' => [
                'isBot' => null,
                'name'  => null,
                'type'  => null,
            ],
        ], $result->toArray());
    }
}
