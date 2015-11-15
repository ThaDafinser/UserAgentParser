<?php
namespace UserAgentParserTest\Provider;

use UAParser\Result;
use UserAgentParser\Provider\UAParser;

/**
 * @covers UserAgentParser\Provider\UAParser
 */
class UAParserTest extends AbstractProviderTestCase
{
    private function getResultMock()
    {
        $ua     = new Result\UserAgent();
        $os     = new Result\OperatingSystem();
        $device = new Result\Device();

        $client         = new Result\Client('');
        $client->ua     = $ua;
        $client->os     = $os;
        $client->device = $device;

        return $client;
    }

    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser($returnValue)
    {
        $parser = $this->getMock('UAParser\Parser', [], [], '', false);
        $parser->expects($this->any())
            ->method('parse')
            ->will($this->returnValue($returnValue));

        return $parser;
    }

    public function testName()
    {
        $provider = new UAParser();

        $this->assertEquals('UAParser', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new UAParser();

        $this->assertEquals('ua-parser/uap-php', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new UAParser();

        $this->assertInternalType('string', $provider->getVersion());
    }

//     /**
//      * @expectedException \UserAgentParser\Exception\NoResultFoundException
//      */
//     public function testNoResultFoundException()
//     {
//         $returnValue = $this->getMock('UAParser\Result\Client', [], [], '', false);

//         $parser = $this->getParser($returnValue);

//         $provider = new UAParser();
//         $provider->setParser($parser);

//         $result = $provider->parse('A real user agent...');
//     }

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
