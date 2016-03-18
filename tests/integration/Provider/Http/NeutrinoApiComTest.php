<?php
namespace UserAgentParserTest\Integration\Provider\Http;

use UserAgentParser\Provider\Http\NeutrinoApiCom;

/**
 * @coversNothing
 */
class NeutrinoApiComTest extends AbstractHttpProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Your API userId "invalid_user" and key "invalid_key" is not valid for NeutrinoApiCom
     */
    public function testInvalidCredentials()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'invalid_user', 'invalid_key');

        $result = $provider->parse('...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        if (! defined('CREDENTIALS_NEUTRINO_API_COM_USER_ID') || ! defined('CREDENTIALS_NEUTRINO_API_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new NeutrinoApiCom($this->getClient(), CREDENTIALS_NEUTRINO_API_COM_USER_ID, CREDENTIALS_NEUTRINO_API_COM_KEY);

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        if (! defined('CREDENTIALS_NEUTRINO_API_COM_USER_ID') || ! defined('CREDENTIALS_NEUTRINO_API_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new NeutrinoApiCom($this->getClient(), CREDENTIALS_NEUTRINO_API_COM_USER_ID, CREDENTIALS_NEUTRINO_API_COM_KEY);

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
                'name'  => 'Googlebot/2.1',
                'type'  => null,
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf('stdClass', $rawResult);
        $this->assertCount(15, (array) $rawResult);

        $this->assertObjectHasAttribute('mobile_screen_height', $rawResult);
        $this->assertObjectHasAttribute('is_mobile', $rawResult);
        $this->assertObjectHasAttribute('type', $rawResult);
        $this->assertObjectHasAttribute('mobile_brand', $rawResult);
        $this->assertObjectHasAttribute('mobile_model', $rawResult);
        $this->assertObjectHasAttribute('version', $rawResult);
        $this->assertObjectHasAttribute('is_android', $rawResult);
        $this->assertObjectHasAttribute('browser_name', $rawResult);
        $this->assertObjectHasAttribute('operating_system_family', $rawResult);
        $this->assertObjectHasAttribute('operating_system_version', $rawResult);
        $this->assertObjectHasAttribute('is_ios', $rawResult);
        $this->assertObjectHasAttribute('producer', $rawResult);
        $this->assertObjectHasAttribute('operating_system', $rawResult);
        $this->assertObjectHasAttribute('mobile_screen_width', $rawResult);
        $this->assertObjectHasAttribute('mobile_browser', $rawResult);
    }

    public function testRealResultDevice()
    {
        if (! defined('CREDENTIALS_NEUTRINO_API_COM_USER_ID') || ! defined('CREDENTIALS_NEUTRINO_API_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new NeutrinoApiCom($this->getClient(), CREDENTIALS_NEUTRINO_API_COM_USER_ID, CREDENTIALS_NEUTRINO_API_COM_KEY);

        $result = $provider->parse('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Mobile Safari',
                'version' => [
                    'major' => 5,
                    'minor' => 1,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.1',
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
                'name'    => 'iOS',
                'version' => [
                    'major' => 5,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.0',
                ],
            ],
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => 'mobile-browser',

                'isMobile' => true,
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
        $this->assertCount(15, (array) $rawResult);
    }
}
