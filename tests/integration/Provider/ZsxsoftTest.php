<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\Zsxsoft;

/**
 * @coversNothing
 */
class ZsxsoftTest extends AbstractProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundWithWarmCache()
    {
        $provider = new Zsxsoft();

        $result = $provider->parse('...');
    }

    public function testRealResultDevice()
    {
        $provider = new Zsxsoft();

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
                'name'    => 'Mac OS X',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'device' => [
                'model' => 'iPhone iOS 5.0',
                'brand' => 'Apple',
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
        $this->assertEquals([
            'browser' => [
                'link'    => 'http://www.apple.com/safari/',
                'title'   => 'Safari 5.1',
                'name'    => 'Safari',
                'version' => '5.1',
                'code'    => 'safari',
                'image'   => 'img/16/browser/safari.png',
            ],
            'os' => [
                'link'    => 'http://www.apple.com/macosx/',
                'name'    => 'Mac OS X',
                'version' => '',
                'code'    => 'mac-3',
                'x64'     => false,
                'title'   => 'Mac OS X',
                'type'    => 'os',
                'dir'     => 'os',
                'image'   => 'img/16/os/mac-3.png',
            ],
            'device' => [
                'link'  => 'http://www.apple.com/iphone',
                'title' => 'Apple iPhone iOS 5.0',
                'model' => 'iPhone iOS 5.0',
                'brand' => 'Apple',
                'code'  => 'iphone',
                'dir'   => 'device',
                'type'  => 'device',
                'image' => 'img/16/device/iphone.png',
            ],
            'platform' => [
                'link'  => 'http://www.apple.com/iphone',
                'title' => 'Apple iPhone iOS 5.0',
                'model' => 'iPhone iOS 5.0',
                'brand' => 'Apple',
                'code'  => 'iphone',
                'dir'   => 'device',
                'type'  => 'device',
                'image' => 'img/16/device/iphone.png',
            ],
        ], $rawResult);
    }
}
