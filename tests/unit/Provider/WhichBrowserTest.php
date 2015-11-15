<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\WhichBrowser;

/**
 * @covers UserAgentParser\Provider\WhichBrowser
 */
class WhichBrowserTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new WhichBrowser();

        $this->assertEquals('WhichBrowser', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new WhichBrowser();

        $this->assertEquals('whichbrowser/whichbrowser', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new WhichBrowser();

        $this->assertInternalType('string', $provider->getVersion());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $userAgent = 'nothing';

        $provider = new WhichBrowser();
        $provider->parse($userAgent);
    }

    public function dataProvider()
    {
        return [
            [
                'userAgent' => 'Googlebot/2.1 (http://www.googlebot.com/bot.html)',
                'result'    => [
                    'bot' => [
                        'isBot' => true,
                        'name'  => 'Googlebot',
                        'type'  => null,
                    ],
                ],
            ],

            [
                'userAgent' => 'Mozilla/5.0 (Linux; U; Android 2.3.4; en-us; Silk/1.1.0-84) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1 Silk-Accelerated=false',
                'result'    => [
                    'browser' => [
                        'name'    => 'Silk',
                        'version' => [
                            'major'    => 1,
                            'minor'    => 1,
                            'patch'    => null,
                            'complete' => '1.1',
                        ],
                    ],

                    'renderingEngine' => [
                        'name'    => 'Webkit',
                        'version' => [
                            'major'    => 533,
                            'minor'    => 1,
                            'patch'    => null,
                            'complete' => '533.1',
                        ],
                    ],

                    'operatingSystem' => [
                        'name'    => 'Android',
                        'version' => [
                            'major'    => 2,
                            'minor'    => 3,
                            'patch'    => 4,
                            'complete' => '2.3.4',
                        ],
                    ],

                    'device' => [
                        'model' => 'Kindle Fire',
                        'brand' => 'Amazon',
                        'type'  => 'tablet',

                        'isMobile' => true,
                        'isTouch'  => null,
                    ],
                ],
            ],

            [
                'userAgent' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.10162',
                'result'    => [
                    'browser' => [
                        'name'    => 'Edge',
                        'version' => [
                            'major'    => null,
                            'minor'    => null,
                            'patch'    => null,
                            'complete' => null,
                        ],
                    ],

                    'renderingEngine' => [
                        'name'    => 'EdgeHTML',
                        'version' => [
                            'major'    => 12,
                            'minor'    => null,
                            'patch'    => null,
                            'complete' => '12',
                        ],
                    ],

                    'operatingSystem' => [
                        'name'    => 'Windows',
                        'version' => [
                            'major'    => 10,
                            'minor'    => null,
                            'patch'    => null,
                            'complete' => '10',
                        ],
                    ],

                    'device' => [
                        'model' => null,
                        'brand' => null,
                        'type'  => 'desktop',

                        'isMobile' => null,
                        'isTouch'  => null,
                    ],
                ],
            ],

            [
                'userAgent' => 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3',

                'result' => [
                    'browser' => [
                        'name'    => 'Safari',
                        'version' => [
                            'major'    => 5,
                            'minor'    => 1,
                            'patch'    => null,
                            'complete' => '5.1',
                        ],
                    ],

                    'renderingEngine' => [
                        'name'    => 'Webkit',
                        'version' => [
                            'major'    => 534,
                            'minor'    => 46,
                            'patch'    => null,
                            'complete' => '534.46',
                        ],
                    ],

                    'operatingSystem' => [
                        'name'    => 'iOS',
                        'version' => [
                            'major'    => 5,
                            'minor'    => 0,
                            'patch'    => null,
                            'complete' => '5.0',
                        ],
                    ],

                    'device' => [
                        'model' => 'iPhone',
                        'brand' => 'Apple',
                        'type'  => 'mobile',

                        'isMobile' => true,
                        'isTouch'  => null,
                    ],
                ],
            ],

            [
                'userAgent' => 'User-Agent: KreaTVWebKit/531 (Motorola STB; Linux)',

                'result' => [
                    'renderingEngine' => [
                        'name'    => 'Webkit',
                        'version' => [
                            'major'    => 531,
                            'minor'    => null,
                            'patch'    => null,
                            'complete' => '531',
                        ],
                    ],

                    'device' => [
                        'model' => null,
                        'brand' => 'Motorola',
                        'type'  => 'television',

                        'isMobile' => null,
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
        $provider = new WhichBrowser();
        $result   = $provider->parse($userAgent);

        $this->assertProviderResult($result, $expectedResult);
    }
}
