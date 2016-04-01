<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\Zsxsoft;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *         
 *          @covers UserAgentParser\Provider\Zsxsoft
 */
class ZsxsoftTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser($returnValue = null)
    {
        $parser = $this->getMock('UserAgent', [
            'analyze',
        ], [], '', false);

        if ($returnValue === null) {
            $parser->data = [
                'browser'  => [],
                'os'       => [],
                'device'   => [],
                'platform' => [],
            ];
        } else {
            $parser->data = $returnValue;
        }

        return $parser;
    }

    public function testPackageNotLoadedException()
    {
        $file     = 'vendor/zsxsoft/php-useragent/composer.json';
        $tempFile = 'vendor/zsxsoft/php-useragent/composer.json.tmp';

        rename($file, $tempFile);

        try {
            $provider = new Zsxsoft();
        } catch (\Exception $ex) {
            // we need to catch the exception, since we need to rename the file again!
        }

        $this->assertInstanceOf('UserAgentParser\Exception\PackageNotLoadedException', $ex);

        rename($tempFile, $file);
    }

    public function testGetName()
    {
        $provider = new Zsxsoft();

        $this->assertEquals('Zsxsoft', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new Zsxsoft();

        $this->assertEquals('https://github.com/zsxsoft/php-useragent', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new Zsxsoft();

        $this->assertEquals('zsxsoft/php-useragent', $provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new Zsxsoft();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new Zsxsoft();

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new Zsxsoft();

        $this->assertEquals([

            'browser' => [
                'name'    => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name'    => false,
                'version' => false,
            ],

            'operatingSystem' => [
                'name'    => true,
                'version' => true,
            ],

            'device' => [
                'model'    => true,
                'brand'    => true,
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
        $provider = new Zsxsoft();

        $this->assertIsRealResult($provider, false, 'unknown');
        $this->assertIsRealResult($provider, false, 'UnKnown');
        $this->assertIsRealResult($provider, true, 'Unknown thing');

        $this->assertIsRealResult($provider, false, 'Mozilla Compatible', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'Mozilla', 'browser', 'name');

        $this->assertIsRealResult($provider, false, 'Browser', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Browser model name', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Android', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Android model name', 'device', 'model');
    }

    public function testParser()
    {
        $provider = new Zsxsoft();
        $this->assertInstanceOf('UserAgent', $provider->getParser());

        $parser = $this->getParser();

        $provider = new Zsxsoft($parser);

        $this->assertSame($parser, $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $provider = new Zsxsoft($this->getParser());

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultBrowserName()
    {
        $result = [
            'browser' => [
                'name'    => 'Mozilla Compatible',
                'version' => '3.2.1',
            ],
            'os'       => [],
            'device'   => [],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $result = $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundExceptionDefaultDeviceModel()
    {
        $result = [
            'browser' => [],
            'os'      => [],
            'device'  => [
                'model' => 'Android',
            ],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Browser only
     */
    public function testParseBrowser()
    {
        $result = [
            'browser' => [
                'name'    => 'Firefox',
                'version' => '3.2.1',
            ],
            'os'       => [],
            'device'   => [],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 2,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.2.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * OS only
     */
    public function testParseOperatingSystem()
    {
        $result = [
            'browser' => [],
            'os'      => [
                'name'    => 'Windows',
                'version' => '7.0.1',
            ],
            'device'   => [],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'Windows',
                'version' => [
                    'major' => 7,
                    'minor' => 0,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '7.0.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only
     */
    public function testParseDevice()
    {
        $result = [
            'browser' => [],
            'os'      => [],
            'device'  => [
                'model' => 'iPhone',
                'brand' => 'Apple',
            ],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => null,

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device model only
     */
    public function testParseDeviceModelOnly()
    {
        $result = [
            'browser' => [],
            'os'      => [],
            'device'  => [
                'model' => 'One+',
            ],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'One+',
                'brand' => null,
                'type'  => null,

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
