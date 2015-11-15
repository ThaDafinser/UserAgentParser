<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\DonatjUAParser;

/**
 * @covers UserAgentParser\Provider\DonatjUAParser
 */
class DonatjUAParserTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new DonatjUAParser();

        $this->assertEquals('DonatjUAParser', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new DonatjUAParser();

        $this->assertEquals('donatj/phpuseragentparser', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new DonatjUAParser();

        $this->assertInternalType('string', $provider->getVersion());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $userAgent = '';

        $provider = new DonatjUAParser();
        $provider->parse($userAgent);
    }

    public function dataProvider()
    {
        return [
            [
                'userAgent' => 'Mozilla/5.0 (Linux; U; Android 2.3.4; en-us; Silk/1.1.0-84) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1 Silk-Accelerated=false',
                'result'    => [
                    'browser' => [
                        'name'    => 'Silk',
                        'version' => [
                            'major'    => 1,
                            'minor'    => 1,
                            'patch'    => 0,
                            'complete' => '1.1.0',
                        ],
                    ],
                ],
            ],

            [
                'userAgent' => 'Mozilla/5.0 (Windows NT 6.1; U; nl; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 Opera 11.01',
                'result'    => [
                    'browser' => [
                        'name'    => 'Opera',
                        'version' => [
                            'major'    => 11,
                            'minor'    => 1,
                            'patch'    => null,
                            'complete' => '11.1',
                        ],
                    ],
                ],
            ],

            [
                'userAgent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15 FirePHP/0.5',
                'result'    => [
                    'browser' => [
                        'name'    => 'Firefox',
                        'version' => [
                            'major'    => 3,
                            'minor'    => 6,
                            'patch'    => 15,
                            'complete' => '3.6.15',
                        ],
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
        $provider = new DonatjUAParser();
        $result   = $provider->parse($userAgent);

        $this->assertProviderResult($result, $expectedResult);
    }
}
