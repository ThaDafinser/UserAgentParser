<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\BrowscapPhp;

/**
 * @covers UserAgentParser\Provider\BrowscapPhp
 */
class BrowscapPhpTest extends AbstractProviderTestCase
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(\stdClass $result)
    {
        $parser = $this->getMock('BrowscapPHP\Browscap');
        $parser->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($result));

        return $parser;
    }

    public function testName()
    {
        $provider = new BrowscapPhp();

        $this->assertEquals('BrowscapPhp', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new BrowscapPhp();

        $this->assertEquals('browscap/browscap-php', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new BrowscapPhp();

        $this->assertNull($provider->getVersion());
    }

    public function testCache()
    {
        $provider = new BrowscapPhp();

        $this->assertNull($provider->getCache());

        $cache = $this->getMock('WurflCache\Adapter\AdapterInterface');
        $provider->setCache($cache);
        $this->assertSame($cache, $provider->getCache());
    }

    /**
     * Real provider with no data
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionRealProvider()
    {
        $provider = new BrowscapPhp();
        $provider->parse('A real user agent...');
    }

    /**
     * Real provider with no data, but with cache
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionRealProviderAndCache()
    {
        $userAgent = 'not valid';

        $cache = $this->getMock('WurflCache\Adapter\AdapterInterface');

        $provider = new BrowscapPhp();
        $provider->setCache($cache);

        $provider->parse($userAgent);
    }

    /**
     * not set
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionNotSet()
    {
        $userAgent = 'not valid';

        $result = new \stdClass();

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));
        $provider->parse($userAgent);
    }

    /**
     * nothing
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionNothing()
    {
        $userAgent = 'not valid';

        $result          = new \stdClass();
        $result->browser = '';

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));
        $provider->parse($userAgent);
    }

    /**
     * unknown
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionUnknown()
    {
        $userAgent = 'not valid';

        $result          = new \stdClass();
        $result->browser = 'unknown';

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));
        $provider->parse($userAgent);
    }

    /**
     * DefaultProperties
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultProperties()
    {
        $userAgent = 'not valid';

        $result          = new \stdClass();
        $result->browser = 'DefaultProperties';

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));
        $provider->parse($userAgent);
    }

    /**
     * Default Browser
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultBrowser()
    {
        $userAgent = 'not valid';

        $result          = new \stdClass();
        $result->browser = 'Default Browser';

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));
        $provider->parse($userAgent);
    }

    /**
     * Bot - Crawler
     */
    public function testParseBotCrawler()
    {
        $result               = new \stdClass();
        $result->browser      = 'Google Bot';
        $result->browser_type = 'Bot/Crawler';
        $result->crawler      = true;

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => 'Google Bot',
                'type'  => 'Crawler',
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Bot - Rss
     */
    public function testParseBotRss()
    {
        $result                      = new \stdClass();
        $result->browser             = 'Hatena RSS';
        $result->browser_type        = 'Bot/Crawler';
        $result->crawler             = true;
        $result->issyndicationreader = true;

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => 'Hatena RSS',
                'type'  => 'RSS',
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Bot - other type
     */
    public function testParseBotOtherType()
    {
        $result               = new \stdClass();
        $result->browser      = 'Hatena RSS';
        $result->browser_type = 'Bot/test';
        $result->crawler      = true;

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => 'Hatena RSS',
                'type'  => 'Bot/test',
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser small
     */
    public function testParseBrowserSmall()
    {
        $result          = new \stdClass();
        $result->browser = 'Midori';
        $result->version = '0.0';
        $result->crawler = false;

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Midori',
                'version' => [
                    'major'    => null,
                    'minor'    => null,
                    'patch'    => null,
                    'complete' => null,
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser with all)
     */
    public function testParseDevice1()
    {
        $result          = new \stdClass();
        $result->browser = 'Midori';
        $result->version = '1.5.2';

        $result->renderingengine_name    = 'WebKit';
        $result->renderingengine_version = '13.0';

        $result->platform         = 'iOS';
        $result->platform_version = '5.0';

        $result->device_name            = 'iPad';
        $result->device_brand_name      = 'Apple';
        $result->device_type            = 'Tablet';
        $result->ismobiledevice         = true;
        $result->device_pointing_method = 'touchscreen';

        $provider = new BrowscapPhp();
        $provider->setParser($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Midori',
                'version' => [
                    'major'    => 1,
                    'minor'    => 5,
                    'patch'    => 2,
                    'complete' => '1.5.2',
                ],
            ],

            'renderingEngine' => [
                'name'    => 'WebKit',
                'version' => [
                    'major'    => 13,
                    'minor'    => 0,
                    'patch'    => null,
                    'complete' => '13.0',
                ],
            ],

            'operatingSystem' => [
                'name'    => 'iOS',
                'version' => [
                    'major'    => 5,
                    'minor'    => 0,
                    'patch'    => null,
                    'complete' => '5.0',
                ],
            ],

            'device' => [
                'model' => 'iPad',
                'brand' => 'Apple',
                'type'  => 'Tablet',

                'isMobile' => true,
                'isTouch'  => true,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
