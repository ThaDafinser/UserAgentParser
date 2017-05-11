<?php
namespace UserAgentParserTest\Integration\Provider;

use BrowserDetector\Detector;
use Psr\Log\NullLogger;
use Psr6NullCache\Adapter\NullCacheItemPool;
use UaResult\Result\Result;
use UserAgentParser\Provider\Mimmi20BrowserDetector;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @coversNothing
 */
class Mimmi20BrowserDetectorTest extends AbstractProviderTestCase
{
    /**
     * Bug: NullCacheItemPool does not work!
     *
     * @expectedException \BrowserDetector\Loader\NotFoundException
     */
    public function testNoResultFoundNullCache()
    {
        $cache  = new NullCacheItemPool();
        $logger = new NullLogger();

        $parser = new Detector($cache, $logger);

        $provider = new Mimmi20BrowserDetector($parser);

        $result = $provider->parse('...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new Mimmi20BrowserDetector();

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new Mimmi20BrowserDetector();

        $result = $provider->parse('Googlebot/2.1 (+http://www.googlebot.com/bot.html)');
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
                'name'  => 'Google Bot',
                'type'  => null,
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertArrayHasKey('request', $rawResult);
        $this->assertArrayHasKey('device', $rawResult);
        $this->assertArrayHasKey('browser', $rawResult);
        $this->assertArrayHasKey('os', $rawResult);
        $this->assertArrayHasKey('engine', $rawResult);
    }

    public function testRealResultDevice()
    {
        $provider = new Mimmi20BrowserDetector();

        $result = $provider->parse('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Safari',
                'version' => [
                    'major' => 5,
                    'minor' => 1,
                    'patch' => 0,

                    'alias' => null,

                    'complete' => '5.1.0',
                ],
            ],
            'renderingEngine' => [
                'name'    => 'WebKit',
                'version' => [
                    'major' => 534,
                    'minor' => 46,
                    'patch' => 0,

                    'alias' => null,

                    'complete' => '534.46.0',
                ],
            ],
            'operatingSystem' => [
                'name'    => 'iOS',
                'version' => [
                    'major' => 5,
                    'minor' => 0,
                    'patch' => 0,

                    'alias' => null,

                    'complete' => '5.0.0',
                ],
            ],
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => 'Mobile Phone',

                'isMobile' => null,
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
