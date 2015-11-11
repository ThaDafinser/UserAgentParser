<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\UAParser;

/**
 * @covers UserAgentParser\Provider\UAParser
 */
class UAParserTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new UAParser();

        $this->assertEquals('UAParser', $provider->getName());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $userAgent = 'nothing';

        $provider = new UAParser();
        $provider->parse($userAgent);
    }

    public function dataProvider()
    {
        return [
            [
                'userAgent' => 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.0.19; aggregator:Spinn3r (Spinn3r 3.1); http://spinn3r.com/robot) Gecko/2010040121 Firefox/3.0.19',
                'result'    => [
                    'bot' => [
                        'isBot' => true,
                        'name'  => 'robot',
                        'type'  => null,
                    ],
                ],
            ],

            [
                'userAgent' => 'Mozilla/5.0 (Linux; U; Android 2.3.4; en-us; Silk/1.1.0-84) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1 Silk-Accelerated=false',
                'result'    => [
                    'browser' => [
                        'name'    => 'Amazon Silk',
                        'version' => [
                            'major'    => 1,
                            'minor'    => 1,
                            'patch'    => 0,
                            'complete' => '1.1.0',
                        ],
                    ],

                    'renderingEngine' => [
                        'name'    => null,
                        'version' => [
                            'major'    => null,
                            'minor'    => null,
                            'patch'    => null,
                            'complete' => null,
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
                        'model' => 'Kindle',
                        'brand' => 'Amazon',
                        'type'  => null,

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
        $provider = new UAParser();
        $result   = $provider->parse($userAgent);

        $this->assertProviderResult($result, $expectedResult);
    }
}
