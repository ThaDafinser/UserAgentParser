<?php
namespace UserAgentParser\Provider
{
    use UserAgentParserTest\Provider\DonatjUAParserTest;

    /**
     * This is need to mock the testing!
     * 
     * @param  string $userAgent
     * @param  array  $result
     * @return array
     */
    function parse_user_agent($userAgent)
    {
        return [
            'browser' => DonatjUAParserTest::$browser,
            'version' => DonatjUAParserTest::$version,
        ];
    }
}

namespace UserAgentParserTest\Provider
{

    use UserAgentParser\Provider\DonatjUAParser;

    /**
     * @covers UserAgentParser\Provider\DonatjUAParser
     */
    class DonatjUAParserTest extends AbstractProviderTestCase
    {
        public static $browser = null;
        public static $version = null;

        public function testName()
        {
            $provider = new DonatjUAParser();

            $this->assertEquals('DonatjUAParser', $provider->getName());
        }

        public function testGetComposerPackageName()
        {
            $provider = new DonatjUAParser();

            $this->assertEquals('donatj/phpuseragentparser', $provider->getComposerPackageName());
        }

        public function testVersion()
        {
            $provider = new DonatjUAParser();

            $this->assertInternalType('string', $provider->getVersion());
        }

        public function testDetectionCapabilities()
        {
            $provider = new DonatjUAParser();

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

        /**
         * @expectedException \UserAgentParser\Exception\NoResultFoundException
         */
        public function testNoResultFoundException()
        {
            self::$browser = null;
            self::$version = null;

            $provider = new DonatjUAParser();

            $result = $provider->parse('A real user agent...');
        }

        /**
         * Browser only
         */
        public function testParseBrowser()
        {
            self::$browser = 'Firefox';
            self::$version = '3.0.1';

            $provider = new DonatjUAParser();

            $result = $provider->parse('A real user agent...');

            // reset
            self::$browser = null;
            self::$version = null;

            $expectedResult = [
                'browser' => [
                    'name'    => 'Firefox',
                    'version' => [
                        'major'    => 3,
                        'minor'    => 0,
                        'patch'    => 1,

                        'alias' => null,

                        'complete' => '3.0.1',
                    ],
                ],
            ];

            $this->assertProviderResult($result, $expectedResult);
        }
    }
}
