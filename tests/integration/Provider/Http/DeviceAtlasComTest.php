<?php
namespace UserAgentParserTest\Integration\Provider\Http;

use UserAgentParser\Provider\Http\DeviceAtlasCom;

/**
 * @coversNothing
 */
class DeviceAtlasComTest extends AbstractHttpProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Your API key "invalid_api_key" is not valid for DeviceAtlasCom
     */
    public function testInvalidCredentials()
    {
        $provider = new DeviceAtlasCom($this->getClient(), 'invalid_api_key');

        $result = $provider->parse('...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        if (! defined('CREDENTIALS_DEVICE_ATLAS_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new DeviceAtlasCom($this->getClient(), CREDENTIALS_DEVICE_ATLAS_COM_KEY);

        $result = $provider->parse('...');
    }

    public function testRealResultDevice()
    {
        if (! defined('CREDENTIALS_DEVICE_ATLAS_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new DeviceAtlasCom($this->getClient(), CREDENTIALS_DEVICE_ATLAS_COM_KEY);

        $result = $provider->parse('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Safari',
                'version' => [
                    'major' => 5,
                    'minor' => 1,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.1',
                ],
            ],
            'renderingEngine' => [
                'name'    => 'WebKit',
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

                    'alias' => '_',

                    'complete' => '5_0',
                ],
            ],
            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'Mobile Phone',

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
        $this->assertCount(6, (array) $rawResult);

        $this->assertObjectHasAttribute('browserVersion', $rawResult);
        $this->assertObjectHasAttribute('osVersion', $rawResult);
        $this->assertObjectHasAttribute('browserName', $rawResult);
        $this->assertObjectHasAttribute('primaryHardwareType', $rawResult);
        $this->assertObjectHasAttribute('browserRenderingEngine', $rawResult);
        $this->assertObjectHasAttribute('osName', $rawResult);
    }
}
