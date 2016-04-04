<?php
namespace UserAgentParserTest\Integration\Provider\Http;

use UserAgentParser\Provider\Http\UserAgentStringCom;

/**
 * @coversNothing
 */
class UserAgentStringComTest extends AbstractHttpProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new UserAgentStringCom($this->getClient());

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new UserAgentStringCom($this->getClient());

        $result = $provider->parse('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $this->assertEquals([
            'browser' => [
                'name'    => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
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
                'isBot' => true,
                'name'  => 'Googlebot',
                'type'  => 'Crawler',
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf('stdClass', $rawResult);
        $this->assertCount(12, (array) $rawResult);

        $this->assertObjectHasAttribute('agent_type', $rawResult);
        $this->assertObjectHasAttribute('agent_name', $rawResult);
        $this->assertObjectHasAttribute('agent_version', $rawResult);
        $this->assertObjectHasAttribute('os_type', $rawResult);
        $this->assertObjectHasAttribute('os_name', $rawResult);
        $this->assertObjectHasAttribute('os_versionName', $rawResult);
        $this->assertObjectHasAttribute('os_versionNumber', $rawResult);
        $this->assertObjectHasAttribute('os_producer', $rawResult);
        $this->assertObjectHasAttribute('os_producerURL', $rawResult);
        $this->assertObjectHasAttribute('linux_distibution', $rawResult);
        $this->assertObjectHasAttribute('agent_language', $rawResult);
        $this->assertObjectHasAttribute('agent_languageTag', $rawResult);
    }

    public function testRealResultDevice()
    {
        $provider = new UserAgentStringCom($this->getClient());

        $result = $provider->parse('Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_3 like Mac OS X; ja-jp) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Safari',
                'version' => [
                    'major' => 5,
                    'minor' => 0,
                    'patch' => 2,

                    'alias' => null,

                    'complete' => '5.0.2',
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
                'name'    => 'iPhone OS',
                'version' => [
                    'major' => 4,
                    'minor' => 3,
                    'patch' => 3,

                    'alias' => null,

                    'complete' => '4.3.3',
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

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf('stdClass', $rawResult);
        $this->assertCount(12, (array) $rawResult);
    }

    public function testEncodeIsCorrect()
    {
        $provider = new UserAgentStringCom($this->getClient());

        $userAgent = 'JUC (Linux; U; 4.0.1; zh-cn; HTC_HD7_4G_T9399+_For_AT&T; 480*800) UCWEB7.9.4.145/139/800';
        $result    = $provider->parse($userAgent);

        $this->assertEquals('UC Browser', $result->getBrowser()->getName());
    }
}
