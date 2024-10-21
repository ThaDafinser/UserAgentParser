<?php

namespace UserAgentParserTest\Integration\Provider\Http;

use UserAgentParser\Provider\Http\UserAgentApiCom;

/**
 * @coversNothing
 *
 * @internal
 */
class UserAgentApiComTest extends AbstractHttpProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Your API key "invalid_api_key" is not valid for UserAgentApiCom
     */
    public function testInvalidCredentials()
    {
        $provider = new UserAgentApiCom($this->getClient(), 'invalid_api_key');

        $result = $provider->parse('...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\RequestException
     * @expectedExceptionMessage User agent is invalid ""
     */
    public function testInvalidUserAgent()
    {
        // rawurlencode() prevents us from this error
        // examples for useragent_invalid
        // https://useragentapi.com/api/v3/json/APIKEY/
        // https://useragentapi.com/api/v3/json/APIKEY//
        $this->markTestIncomplete('User agent is invalid only occure if the USERAGENT is not given or a wrong character.');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        if (!\defined('CREDENTIALS_USER_AGENT_API_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new UserAgentApiCom($this->getClient(), CREDENTIALS_USER_AGENT_API_COM_KEY);

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        if (!\defined('CREDENTIALS_USER_AGENT_API_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new UserAgentApiCom($this->getClient(), CREDENTIALS_USER_AGENT_API_COM_KEY);

        $result = $provider->parse('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $this->assertEquals([
            'browser' => [
                'name' => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'renderingEngine' => [
                'name' => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'operatingSystem' => [
                'name' => null,
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
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
            'bot' => [
                'isBot' => true,
                'name' => 'Googlebot',
                'type' => null,
            ],
        ], $result->toArray());

        // Test the raw result
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf('stdClass', $rawResult);
        $this->assertCount(3, (array) $rawResult);

        $this->assertObjectHasAttribute('platform_name', $rawResult);
        $this->assertObjectHasAttribute('platform_version', $rawResult);
        $this->assertObjectHasAttribute('platform_type', $rawResult);
    }

    public function testRealResultDevice()
    {
        if (!\defined('CREDENTIALS_USER_AGENT_API_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new UserAgentApiCom($this->getClient(), CREDENTIALS_USER_AGENT_API_COM_KEY);

        $result = $provider->parse('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3');
        $this->assertEquals([
            'browser' => [
                'name' => 'Safari',
                'version' => [
                    'major' => 7534,
                    'minor' => 48,
                    'patch' => 3,

                    'alias' => null,

                    'complete' => '7534.48.3',
                ],
            ],
            'renderingEngine' => [
                'name' => 'WebKit',
                'version' => [
                    'major' => 534,
                    'minor' => 46,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '534.46',
                ],
            ],
            'operatingSystem' => [
                'name' => null,
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
                'type' => 'Mobile',

                'isMobile' => null,
                'isTouch' => null,
            ],
            'bot' => [
                'isBot' => null,
                'name' => null,
                'type' => null,
            ],
        ], $result->toArray());

        // Test the raw result
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf('stdClass', $rawResult);
        $this->assertCount(7, (array) $rawResult);

        $this->assertObjectHasAttribute('platform_name', $rawResult);
        $this->assertObjectHasAttribute('platform_version', $rawResult);
        $this->assertObjectHasAttribute('platform_type', $rawResult);

        $this->assertObjectHasAttribute('browser_name', $rawResult);
        $this->assertObjectHasAttribute('browser_version', $rawResult);
        $this->assertObjectHasAttribute('engine_name', $rawResult);
        $this->assertObjectHasAttribute('engine_version', $rawResult);
    }

    public function testEncodeIsCorrect()
    {
        if (!\defined('CREDENTIALS_USER_AGENT_API_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new UserAgentApiCom($this->getClient(), CREDENTIALS_USER_AGENT_API_COM_KEY);

        $userAgent = 'Mozilla/5.0 (Linux; U; Android 3.0.1; en-us; HTC T9299+ For AT&T Build/GRJ22) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1';
        $result = $provider->parse($userAgent);

        $this->assertEquals('WebKit', $result->getRenderingEngine()
            ->getName());
    }
}
