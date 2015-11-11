<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\PiwikDeviceDetector;

/**
 * @covers UserAgentParser\Provider\PiwikDeviceDetector
 */
class PiwikDeviceDetectorTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new PiwikDeviceDetector();

        $this->assertEquals('PiwikDeviceDetector', $provider->getName());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $userAgent = 'not valid';

        $provider = new PiwikDeviceDetector();
        $provider->parse($userAgent);
    }

    public function dataProvider()
    {
        return [
            [
                'userAgent' => 'Aboundex/0.3 (http://www.aboundex.com/crawler/)',
                'result'    => [
                    'bot' => [
                        'isBot' => true,
                        'name'  => 'Aboundexbot',
                        'type'  => 'Search bot',
                    ],
                ],
            ],

            [
                'userAgent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A405 Safari/8536.25',
                'result'    => [
                    'browser' => [
                        'name'    => 'Mobile Safari',
                        'version' => [
                            'major'    => 6,
                            'minor'    => 0,
                            'patch'    => null,
                            'complete' => '6.0',
                        ],
                    ],

                    'renderingEngine' => [
                        'name'    => 'WebKit',
                        'version' => [
                            'major'    => null,
                            'minor'    => null,
                            'patch'    => null,
                            'complete' => null,
                        ],
                    ],

                    'operatingSystem' => [
                        'name'    => 'iOS',
                        'version' => [
                            'major'    => 6,
                            'minor'    => 0,
                            'patch'    => null,
                            'complete' => '6.0',
                        ],
                    ],

                    'device' => [
                        'model' => 'iPhone',
                        'brand' => 'Apple',
                        'type'  => 'smartphone',

                        'isMobile' => true,
                        'isTouch'  => null,
                    ],
                ],
            ],

            [
                'userAgent' => 'Mozilla/5.0 (iPad; U; CPU iPhone OS 5_1_1 like Mac OS X; en-us)',
                'result'    => [
                    'browser' => [
                        'name'    => 'Mobile Safari',
                        'version' => [
                            'major'    => null,
                            'minor'    => null,
                            'patch'    => null,
                            'complete' => null,
                        ],
                    ],

                    'renderingEngine' => [
                        'name'    => 'WebKit',
                        'version' => [
                            'major'    => null,
                            'minor'    => null,
                            'patch'    => null,
                            'complete' => null,
                        ],
                    ],

                    'operatingSystem' => [
                        'name'    => 'iOS',
                        'version' => [
                            'major'    => 5,
                            'minor'    => 1,
                            'patch'    => 1,
                            'complete' => '5.1.1',
                        ],
                    ],

                    'device' => [
                        'model' => 'iPad',
                        'brand' => 'Apple',
                        'type'  => 'tablet',

                        'isMobile' => true,
                        'isTouch'  => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAllParseResults($userAgent, $expectedResult)
    {
        $provider = new PiwikDeviceDetector();
        $result   = $provider->parse($userAgent);

        $this->assertProviderResult($result, $expectedResult);
    }
}
