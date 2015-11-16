<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\YzalisUAParser;

/**
 * @covers UserAgentParser\Provider\YzalisUAParser
 */
class YzalisUAParserTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new YzalisUAParser();

        $this->assertEquals('YzalisUAParser', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new YzalisUAParser();

        $this->assertEquals('yzalis/ua-parser', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new YzalisUAParser();

        $this->assertInternalType('string', $provider->getVersion());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $provider = new YzalisUAParser();
        $provider->parse('A real user agent...');
    }

    public function dataProvider()
    {
        return [
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
                        'name'    => 'WebKit',
                        'version' => [
                            'major'    => 536,
                            'minor'    => 26,
                            'patch'    => null,
                            'complete' => '536.26',
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
                        'type'  => 'mobile',

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
        $provider = new YzalisUAParser();
        $result   = $provider->parse($userAgent);

        $this->assertProviderResult($result, $expectedResult);
    }
}
