<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\Woothee;

/**
 * @covers UserAgentParser\Provider\Woothee
 */
class WootheeTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new Woothee();

        $this->assertEquals('Woothee', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new Woothee();

        $this->assertEquals('woothee/woothee', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new Woothee();

        $this->assertInternalType('string', $provider->getVersion());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $userAgent = 'nothing';

        $provider = new Woothee();
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
                        'name'  => 'misc crawler',
                        'type'  => null,
                    ],
                ],
            ],

            [
                'userAgent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A405 Safari/8536.25',
                'result'    => [
                    'browser' => [
                        'name'    => 'Safari',
                        'version' => [
                            'major'    => 6,
                            'minor'    => 0,
                            'patch'    => null,
                            'complete' => '6.0',
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
                        'name'    => null,
                        'version' => [
                            'major'    => 6,
                            'minor'    => 0,
                            'patch'    => null,
                            'complete' => '6.0',
                        ],
                    ],

                    'device' => [
                        'model' => null,
                        'brand' => null,
                        'type'  => null,

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
        $provider = new Woothee();
        $result   = $provider->parse($userAgent);

        $this->assertProviderResult($result, $expectedResult);
    }
}
