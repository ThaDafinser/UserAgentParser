<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\BrowscapPhp;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers UserAgentParser\Provider\AbstractBrowscap
 */
class AbstractBrowscapTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(\stdClass $result = null, $date = null)
    {
        if ($date === null) {
            $date = new \DateTime('2016-03-10 18:00:00');
        }

        $cache = self::createMock('BrowscapPHP\Cache\BrowscapCache');
        $cache->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(''));
        $cache->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('321'));
        $cache->expects($this->any())
            ->method('getReleaseDate')
            ->will($this->returnValue($date->format('r')));

        $parser = self::createMock('BrowscapPHP\Browscap');
        $parser->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue($cache));
        $parser->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($result));

        return $parser;
    }

    /**
     * Warm cache is missing!
     *
     * @expectedException \UserAgentParser\Exception\InvalidArgumentException
     * @expectedExceptionMessage You need to warm-up the cache first to use this provider
     */
    public function testConstructExceptionNOWarmCache()
    {
        $cache = self::createMock('BrowscapPHP\Cache\BrowscapCache');

        $parser = self::createMock('BrowscapPHP\Browscap');
        $parser->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue($cache));

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $parser,
            'anotherExceptedType',
        ]);
    }

    /**
     * Different type
     *
     * @expectedException \UserAgentParser\Exception\InvalidArgumentException
     * @expectedExceptionMessage Expected the "anotherExceptedType" data file. Instead got the "" data file
     */
    public function testConstructException()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser(),
            'anotherExceptedType',
        ]);
    }

    public function testGetName()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser(),
        ]);

        $this->assertNull($provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser(),
        ]);

        $this->assertEquals('https://github.com/browscap/browscap-php', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser(),
        ]);

        $this->assertEquals('browscap/browscap-php', $provider->getPackageName());
    }

    public function testVersion()
    {
        $parser = $this->getParser();

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $parser,
        ]);

        $this->assertEquals('321', $provider->getVersion());
    }

    public function testUpdateDate()
    {
        $date = new \DateTime('2016-03-10 18:00:00');

        $parser = $this->getParser(null, $date);

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $parser,
        ]);

        $actualDate = $provider->getUpdateDate();

        $this->assertInstanceOf('DateTime', $actualDate);
        $this->assertEquals($date->format('Y-m-d H:i:s'), $actualDate->format('Y-m-d H:i:s'));
    }

    public function testDetectionCapabilities()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser(),
        ]);

        $this->assertEquals([

            'browser' => [
                'name'    => false,
                'version' => false,
            ],

            'renderingEngine' => [
                'name'    => false,
                'version' => false,
            ],

            'operatingSystem' => [
                'name'    => false,
                'version' => false,
            ],

            'device' => [
                'model'    => false,
                'brand'    => false,
                'type'     => false,
                'isMobile' => false,
                'isTouch'  => false,
            ],

            'bot' => [
                'isBot' => false,
                'name'  => false,
                'type'  => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function testIsRealResult()
    {
        $parser = $this->getParser();

        $provider = new BrowscapPhp($parser);

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'unknown');
        $this->assertIsRealResult($provider, true, 'unknown something');
        $this->assertIsRealResult($provider, true, 'something unknown');

        /*
         * browser name
         */
        $this->assertIsRealResult($provider, false, 'Default Browser', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'Default Browser something', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'something Default Browser', 'browser', 'name');

        /*
         * device model
         */
        $this->assertIsRealResult($provider, false, 'general', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'general something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something general', 'device', 'model');

        /*
         * bot name
         */
        $this->assertIsRealResult($provider, false, 'General Crawlers', 'bot', 'name');
        $this->assertIsRealResult($provider, false, 'General Crawlers something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something General Crawlers', 'bot', 'name');

        $this->assertIsRealResult($provider, false, 'Generic', 'bot', 'name');
        $this->assertIsRealResult($provider, false, 'Generic something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something Generic', 'bot', 'name');
    }

    public function testParser()
    {
        $parser = $this->getParser();

        $provider = new BrowscapPhp($parser);

        $this->assertSame($parser, $provider->getParser());
    }

    /**
     * Provider no result
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $result = new \stdClass();

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

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

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

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

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

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

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function testProviderNameAndVersionIsInResult()
    {
        $result               = new \stdClass();
        $result->browser      = 'Google Bot';
        $result->browser_type = 'Crawler';
        $result->crawler      = true;

        $provider = $this->getMockBuilder('UserAgentParser\Provider\AbstractBrowscap')
        ->setConstructorArgs([$this->getParser($result)])
        ->setMethods(['getName'])
        ->getMockForAbstractClass();

        $provider->expects($this->any())
        ->method('getName')
        ->will($this->returnValue('Browscap'));

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('Browscap', $result->getProviderName());
        $this->assertRegExp('/\d{1,}$/', $result->getProviderVersion());
    }

    /**
     * Bot - Crawler
     */
    public function testParseBotCrawler()
    {
        $result               = new \stdClass();
        $result->browser      = 'Google Bot';
        $result->browser_type = 'Crawler';
        $result->crawler      = true;

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

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

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

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

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

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
     * Bot - name default
     */
    public function testParseBotNameDefault()
    {
        $result          = new \stdClass();
        $result->browser = 'General Crawlers';
        $result->crawler = true;

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => null,
                'type'  => null,
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

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Midori',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser with all
     */
    public function testParseAll()
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

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Midori',
                'version' => [
                    'major' => 1,
                    'minor' => 5,
                    'patch' => 2,

                    'alias' => null,

                    'complete' => '1.5.2',
                ],
            ],

            'renderingEngine' => [
                'name'    => 'WebKit',
                'version' => [
                    'major' => 13,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '13.0',
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
                'model' => 'iPad',
                'brand' => 'Apple',
                'type'  => 'Tablet',

                'isMobile' => true,
                'isTouch'  => true,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device - model default
     */
    public function testParseDeviceModelDefault()
    {
        $result              = new \stdClass();
        $result->browser     = 'Midori';
        $result->device_name = 'general';

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Midori',
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
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device - model default
     */
    public function testParseDeviceModelDefault2()
    {
        $result              = new \stdClass();
        $result->browser     = 'Midori';
        $result->device_name = 'desktop';

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractBrowscap', [
            $this->getParser($result),
        ]);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Midori',
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
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
