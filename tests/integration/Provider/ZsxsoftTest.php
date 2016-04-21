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

        $result = $provider->parse('Mozilla/5.0 (Linux; Android 4.3; SCH-R970C Build/JSS15J) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.114 Mobile Safari/537.3');
        $this->assertEquals([
            'browser' => [
                'name'    => 'Google Chrome',
                'version' => [
                    'major' => 34,
                    'minor' => 0,
                    'patch' => 1847,

                    'alias' => null,

                    'complete' => '34.0.1847.114',
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
                    'major' => 4,
                    'minor' => 3,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '4.3',
                ],
            ],
            'device' => [
                'model' => 'R970C',
                'brand' => 'Samsung',
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
                'link'    => 'http://google.com/chrome/',
                'title'   => 'Google Chrome 34.0.1847.114',
                'name'    => 'Google Chrome',
                'version' => '34.0.1847.114',
                'code'    => 'chrome',
                'image'   => 'img/16/browser/chrome.png',
            ],
            'os' => [
                'link'    => 'http://www.android.com/',
                'name'    => 'Android',
                'version' => '4.3',
                'code'    => 'android',
                'x64'     => false,
                'title'   => 'Android 4.3',
                'type'    => 'os',
                'dir'     => 'os',
                'image'   => 'img/16/os/android.png',
            ],
            'device' => [
                'link'  => 'http://www.samsungmobile.com/',
                'title' => 'Samsung R970C',
                'model' => 'R970C',
                'brand' => 'Samsung',
                'code'  => 'samsung',
                'dir'   => 'device',
                'type'  => 'device',
                'image' => 'img/16/device/samsung.png',
            ],
            'platform' => [
                'link'  => 'http://www.samsungmobile.com/',
                'title' => 'Samsung R970C',
                'model' => 'R970C',
                'brand' => 'Samsung',
                'code'  => 'samsung',
                'dir'   => 'device',
                'type'  => 'device',
                'image' => 'img/16/device/samsung.png',
            ],
        ], $rawResult);
    }
}
