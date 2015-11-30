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
    private function getParser(\stdClass $result = null)
    {
        $parser = $this->getMock('BrowscapPHP\Browscap');
        $parser->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($result));

        return $parser;
    }

    public function testName()
    {
        $provider = new BrowscapPhp($this->getParser());

        $this->assertEquals('BrowscapPhp', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new BrowscapPhp($this->getParser());

        $this->assertEquals('browscap/browscap-php', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $cache = $this->getMock('BrowscapPHP\Cache\BrowscapCache', [], [], '', false);
        $cache->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('321'));

        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue($cache));

        $provider = new BrowscapPhp($parser);

        $this->assertEquals('321', $provider->getVersion());
    }

    public function testParser()
    {
        $parser = $this->getParser();

        $provider = new BrowscapPhp($parser);

        $this->assertSame($parser, $provider->getParser());
    }

    /**
     * Default provider with no data
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultProvider()
    {
        $result = new \stdClass();

        $provider = new BrowscapPhp($this->getParser($result));

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider no result
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionRealProvider()
    {
        $result = new \stdClass();

        $parser = $this->getParser($result);

        $provider = new BrowscapPhp($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider result empty
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionResultEmpty()
    {
        $result          = new \stdClass();
        $result->browser = '';

        $parser = $this->getParser($result);

        $provider = new BrowscapPhp($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider result unknown
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionResultUnknown()
    {
        $result          = new \stdClass();
        $result->browser = 'unknown';

        $parser = $this->getParser($result);

        $provider = new BrowscapPhp($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider result DefaultProperties
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionResultDefaultProperties()
    {
        $result          = new \stdClass();
        $result->browser = 'DefaultProperties';

        $parser = $this->getParser($result);

        $provider = new BrowscapPhp($parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider result Default Browser
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionResultDefaultBrowser()
    {
        $result          = new \stdClass();
        $result->browser = 'Default Browser';

        $parser = $this->getParser($result);

        $provider = new BrowscapPhp($parser);

        $result = $provider->parse('A real user agent...');
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

        $provider = new BrowscapPhp($this->getParser($result));

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

        $provider = new BrowscapPhp($this->getParser($result));

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

        $provider = new BrowscapPhp($this->getParser($result));

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

        $provider = new BrowscapPhp($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Midori',
                'version' => [
                    'major'    => null,
                    'minor'    => null,
                    'patch'    => null,

                    'alias' => null,

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

        $provider = new BrowscapPhp($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Midori',
                'version' => [
                    'major'    => 1,
                    'minor'    => 5,
                    'patch'    => 2,

                    'alias' => null,

                    'complete' => '1.5.2',
                ],
            ],

            'renderingEngine' => [
                'name'    => 'WebKit',
                'version' => [
                    'major'    => 13,
                    'minor'    => 0,
                    'patch'    => null,

                    'alias' => null,

                    'complete' => '13.0',
                ],
            ],

            'operatingSystem' => [
                'name'    => 'iOS',
                'version' => [
                    'major'    => 5,
                    'minor'    => 0,
                    'patch'    => null,

                    'alias' => null,

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
