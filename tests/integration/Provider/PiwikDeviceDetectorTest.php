<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\PiwikDeviceDetector;

/**
 * @coversNothing
 */
class PiwikDeviceDetectorTest extends AbstractProviderTestCase
{
    public function testMethods()
    {
        $provider = new PiwikDeviceDetector();
        $parser   = $provider->getParser();

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('setUserAgent'), 'method setUserAgent() does not exist anymore');
        $this->assertTrue($class->hasMethod('parse'), 'method parse() does not exist anymore');

        $this->assertTrue($class->hasMethod('isBot'), 'method isBot() does not exist anymore');
        $this->assertTrue($class->hasMethod('getBot'), 'method getBot() does not exist anymore');

        $this->assertTrue($class->hasMethod('getClient'), 'method getClient() does not exist anymore');
        $this->assertTrue($class->hasMethod('getOs'), 'method getOs() does not exist anymore');

        $this->assertTrue($class->hasMethod('getModel'), 'method getModel() does not exist anymore');
        $this->assertTrue($class->hasMethod('getBrandName'), 'method getBrandName() does not exist anymore');
        $this->assertTrue($class->hasMethod('getDeviceName'), 'method getDeviceName() does not exist anymore');
        $this->assertTrue($class->hasMethod('isMobile'), 'method isMobile() does not exist anymore');
        $this->assertTrue($class->hasMethod('isTouchEnabled'), 'method isTouchEnabled() does not exist anymore');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new PiwikDeviceDetector();

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new PiwikDeviceDetector();

        $result = $provider->parse('Googlebot/2.1 (+http://www.googlebot.com/bot.html)');
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
                'type'  => 'Search bot',
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();
        $this->assertEquals([
            'client'          => null,
            'operatingSystem' => null,

            'device' => [
                'brand'     => null,
                'brandName' => null,

                'model' => null,

                'device'     => null,
                'deviceName' => null,
            ],

            'bot' => [
                'name'     => 'Googlebot',
                'category' => 'Search bot',
                'url'      => 'http://www.google.com/bot.html',
                'producer' => [
                    'name' => 'Google Inc.',
                    'url'  => 'http://www.google.com',
                ],
            ],

            'extra' => [
                'isBot' => true,

                // client
                'isBrowser'     => false,
                'isFeedReader'  => false,
                'isMobileApp'   => false,
                'isPIM'         => false,
                'isLibrary'     => false,
                'isMediaPlayer' => false,

                // deviceType
                'isCamera'              => false,
                'isCarBrowser'          => false,
                'isConsole'             => false,
                'isFeaturePhone'        => false,
                'isPhablet'             => false,
                'isPortableMediaPlayer' => false,
                'isSmartDisplay'        => false,
                'isSmartphone'          => false,
                'isTablet'              => false,
                'isTV'                  => false,

                // other special
                'isDesktop'      => false,
                'isMobile'       => false,
                'isTouchEnabled' => false,
            ],
        ], $rawResult);
    }

    public function testRealResultDevice()
    {
        $provider = new PiwikDeviceDetector();

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
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.0',
                ],
            ],
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => 'smartphone',

                'isMobile' => true,
                'isTouch'  => null,
            ],
            'bot' => [
                'isBot' => null,
                'name'  => null,
                'type'  => null,
            ],
        ], $result->toArray());
    }
}
