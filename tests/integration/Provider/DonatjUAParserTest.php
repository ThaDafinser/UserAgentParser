<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\DonatjUAParser;

/**
 * @coversNothing
 */
class DonatjUAParserTest extends AbstractProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new DonatjUAParser();

        $result = $provider->parse('...');
    }

    public function testRealResult()
    {
        $provider = new DonatjUAParser();

        $result = $provider->parse('Mozilla/5.0 (X11; U; CrOS i686 0.9.128; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.339');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Chrome',
                'version' => [
                    'major' => 8,
                    'minor' => 0,
                    'patch' => 552,

                    'alias' => null,

                    'complete' => '8.0.552.339',
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
                'isBot' => null,
                'name'  => null,
                'type'  => null,
            ],
        ], $result->toArray());

        $this->assertEquals([
            'platform' => 'Chrome OS',
            'browser'  => 'Chrome',
            'version'  => '8.0.552.339',
        ], $result->getProviderResultRaw());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertEquals([
            'platform' => 'Chrome OS',
            'browser'  => 'Chrome',
            'version'  => '8.0.552.339',
        ], $rawResult);
    }
}
