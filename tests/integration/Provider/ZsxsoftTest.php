<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\Zsxsoft;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * 
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

        $result = $provider->parse('Mozilla/5.0 (Linux; Android 5.0.1; Nexus 7 Build/LRX22C) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.59 Safari/537.36 OPR/26.0.1656.87080');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Opera',
                'version' => [
                    'major' => 26,
                    'minor' => 0,
                    'patch' => 1656,

                    'alias' => null,

                    'complete' => '26.0.1656.87080',
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
                'name'    => 'Android',
                'version' => [
                    'major' => 5,
                    'minor' => 0,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '5.0.1',
                ],
            ],
            'device' => [
                'model' => 'Nexus 7',
                'brand' => 'Google',
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
                'link'    => 'http://www.opera.com/',
                'title'   => 'Opera 26.0.1656.87080',
                'name'    => 'Opera',
                'version' => '26.0.1656.87080',
                'code'    => 'opera-1',
                'image'   => 'img/16/browser/opera-1.png',
            ],
            'os' => [
                'link'    => 'http://www.android.com/',
                'name'    => 'Android',
                'version' => '5.0.1',
                'code'    => 'android',
                'x64'     => false,
                'title'   => 'Android 5.0.1',
                'type'    => 'os',
                'dir'     => 'os',
                'image'   => 'img/16/os/android.png',
            ],
            'device' => [
                'link'  => 'https://www.google.com/nexus/',
                'title' => 'Google Nexus 7',
                'model' => 'Nexus 7',
                'brand' => 'Google',
                'code'  => 'google-nexusone',
                'dir'   => 'device',
                'type'  => 'device',
                'image' => 'img/16/device/google-nexusone.png',
            ],
            'platform' => [
                'link'  => 'https://www.google.com/nexus/',
                'title' => 'Google Nexus 7',
                'model' => 'Nexus 7',
                'brand' => 'Google',
                'code'  => 'google-nexusone',
                'dir'   => 'device',
                'type'  => 'device',
                'image' => 'img/16/device/google-nexusone.png',
            ],
        ], $rawResult);
    }
}
