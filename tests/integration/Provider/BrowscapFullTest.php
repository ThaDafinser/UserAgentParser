<?php
namespace UserAgentParserTest\Integration\Provider;

use BrowscapPHP\Browscap;
use UserAgentParser\Provider\BrowscapFull;

/**
 * @coversNothing
 */
class BrowscapFullTest extends AbstractProviderTestCase
{
    private function getParserWithWarmCache($type)
    {
        $filename = 'php_browscap.ini';
        if ($type != '') {
            $filename = $type . '_' . $filename;
        }

        $cache = new \WurflCache\Adapter\Memory();

        $browscap = new Browscap();
        $browscap->setCache($cache);
        $browscap->convertFile('tests/resources/browscap/' . $filename);

        return $browscap;
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
                'name'  => 'AskJeeves',
                'type'  => 'Bot/Crawler',
            ],
        ], $result->toArray());

        $rawResult = $result->getProviderResultRaw();
        $this->assertInstanceOf('stdClass', $rawResult);
    }

    public function testRealResultDevice()
    {
        $provider = new BrowscapFull($this->getParserWithWarmCache('full'));

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
                'model' => 'Smart TV',
                'brand' => 'Samsung',
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
