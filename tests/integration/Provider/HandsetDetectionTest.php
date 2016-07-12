<?php
namespace UserAgentParserTest\Integration\Provider;

use HandsetDetection as Parser;
use UserAgentParser\Provider\HandsetDetection;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *         
 *          @coversNothing
 */
class HandsetDetectionTest extends AbstractProviderTestCase
{
    private function getParser()
    {
        $config = [
            'username' => 'something',
            'secret'   => 'something',

            'use_local' => true,
            'filesdir'  => 'tests/resources/handset-detection',

            'log_unknown' => false,

            'cache' => [
                'file' => [],
            ],
        ];

        $parser = new Parser\HD4($config);

        return $parser;
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new HandsetDetection($this->getParser());

        $result = $provider->parse('...');
    }

    public function testRealResult()
    {
        $provider = new HandsetDetection($this->getParser());

        $result = $provider->parse('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Mobile Safari',
                'version' => [
                    'major' => 5,
                    'minor' => 1,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.1',
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
                    'major' => 5,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.0',
                ],
            ],
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => null,

                'isMobile' => null,
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

        $this->assertInternalType('array', $rawResult);
        $this->assertCount(48, $rawResult);

        $this->assertArrayHasKey('general_vendor', $rawResult);
        $this->assertArrayHasKey('general_model', $rawResult);

        $this->assertArrayHasKey('general_platform', $rawResult);
        $this->assertArrayHasKey('general_platform_version', $rawResult);

        $this->assertArrayHasKey('general_browser', $rawResult);
        $this->assertArrayHasKey('general_browser_version', $rawResult);
    }
}
