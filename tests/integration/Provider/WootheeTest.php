<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\Woothee;

/**
 * @coversNothing
 */
class WootheeTest extends AbstractProviderTestCase
{
    public function testMethodParse()
    {
        $provider = new Woothee();
        $parser   = $provider->getParser();

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('parse'), 'method parse() does not exist anymore');

        /*
         * test paramters
         */
        $method     = $class->getMethod('parse');
        $parameters = $method->getParameters();

        $this->assertEquals(1, count($parameters));
    }

    public function testParseResult()
    {
        $provider = new Woothee();
        $parser   = $provider->getParser();

        /* @var $result \UAParser\Result\Result */
        $result = $parser->parse('A real user agent...');

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('os', $result);
        $this->assertArrayHasKey('os_version', $result);
        $this->assertArrayHasKey('vendor', $result);
        $this->assertArrayHasKey('category', $result);
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new Woothee();

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new Woothee();

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
                'name'  => null,
                'type'  => null,
            ],
        ], $result->toArray());
    }

    public function testRealResultDevice()
    {
        $provider = new Woothee();

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
                'type'  => 'smartphone',

                'isMobile' => null,
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
