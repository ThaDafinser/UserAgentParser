<?php
namespace UserAgentParserTest\Provider;

use UAParser\Result;
use UserAgentParser\Provider\UAParser;

/**
 * @covers UserAgentParser\Provider\UAParser
 */
class UAParserTest extends AbstractProviderTestCase
{
    /**
     *
     * @return \UAParser\Result\Client
     */
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
    private function getParser($returnValue = null)
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

    public function testParser()
    {
        $parser = $this->getParser();

        $provider = new UAParser();
        $provider->setParser($parser);

        $this->assertSame($parser, $provider->getParser());

        $provider->setParser(null);
        $this->assertInstanceOf('UAParser\Parser', $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $parser = $this->getParser($this->getResultMock());

        $provider = new UAParser();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $result                 = $this->getResultMock();
        $result->device->family = 'Spider';
        $result->ua->family     = 'Googlebot';

        $parser = $this->getParser($result);

        $provider = new UAParser();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => 'Googlebot',
                'type'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only
     */
    public function testParseBrowser()
    {
        $result             = $this->getResultMock();
        $result->ua->family = 'Firefox';
        $result->ua->major  = 3;
        $result->ua->minor  = 2;
        $result->ua->patch  = 1;

        $parser = $this->getParser($result);

        $provider = new UAParser();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Firefox',
                'version' => [
                    'major'    => 3,
                    'minor'    => 2,
                    'patch'    => 1,

                    'alias' => null,

                    'complete' => '3.2.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * OS only
     */
    public function testParseOperatingSystem()
    {
        $result             = $this->getResultMock();
        $result->os->family = 'Windows';
        $result->os->major  = 7;
        $result->os->minor  = 0;
        $result->os->patch  = 1;

        $parser = $this->getParser($result);

        $provider = new UAParser();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'Windows',
                'version' => [
                    'major'    => 7,
                    'minor'    => 0,
                    'patch'    => 1,

                    'alias' => null,

                    'complete' => '7.0.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only
     */
    public function testParseDevice()
    {
        $result                = $this->getResultMock();
        $result->device->model = 'iPhone';
        $result->device->brand = 'Apple';

        $parser = $this->getParser($result);

        $provider = new UAParser();
        $provider->setParser($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => null,

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
