<?php

namespace UserAgentParserTest\Integration\Provider;

use ReflectionClass;
use UserAgentParser\Provider\UAParser;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @coversNothing
 *
 * @internal
 */
class UAParserTest extends AbstractProviderTestCase
{
    public function testMethodParse()
    {
        $provider = new UAParser($this->getParser());
        $parser = $provider->getParser();

        // test method exists
        $class = new ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('parse'), 'method parse() does not exist anymore');

        // test paramters
        $method = $class->getMethod('parse');
        $parameters = $method->getParameters();

        $this->assertEquals(2, \count($parameters));

        // @var $optionalPara \ReflectionParameter
        $optionalPara = $parameters[1];

        $this->assertTrue($optionalPara->isOptional(), '2nd parameter of parse() is not optional anymore');
    }

    public function testParseResult()
    {
        $provider = new UAParser($this->getParser());
        $parser = $provider->getParser();

        // @var $result \UAParser\Result\Client
        $result = $parser->parse('A real user agent...');

        $this->assertInstanceOf('UAParser\Result\Client', $result);

        $class = new ReflectionClass($result);

        $this->assertTrue($class->hasProperty('ua'), 'property ua does not exist anymore');
        $this->assertInstanceOf('UAParser\Result\UserAgent', $result->ua);

        $this->assertTrue($class->hasProperty('os'), 'property os does not exist anymore');
        $this->assertInstanceOf('UAParser\Result\OperatingSystem', $result->os);

        $this->assertTrue($class->hasProperty('device'), 'property os does not exist anymore');
        $this->assertInstanceOf('UAParser\Result\Device', $result->device);
    }

    public function testClassBrowserResult()
    {
        $class = new ReflectionClass('UAParser\Result\OperatingSystem');

        $this->assertTrue($class->hasProperty('family'), 'property family does not exist anymore');
        $this->assertTrue($class->hasProperty('major'), 'property major does not exist anymore');
        $this->assertTrue($class->hasProperty('minor'), 'property minor does not exist anymore');
        $this->assertTrue($class->hasProperty('patch'), 'property patch does not exist anymore');
    }

    public function testClassOsResult()
    {
        $class = new ReflectionClass('UAParser\Result\UserAgent');

        $this->assertTrue($class->hasProperty('family'), 'property family does not exist anymore');
        $this->assertTrue($class->hasProperty('major'), 'property major does not exist anymore');
        $this->assertTrue($class->hasProperty('minor'), 'property minor does not exist anymore');
        $this->assertTrue($class->hasProperty('patch'), 'property patch does not exist anymore');
    }

    public function testClassDeviceResult()
    {
        $class = new ReflectionClass('UAParser\Result\Device');

        $this->assertTrue($class->hasProperty('model'), 'property family does not exist anymore');
        $this->assertTrue($class->hasProperty('brand'), 'property major does not exist anymore');
        $this->assertTrue($class->hasProperty('family'), 'property family does not exist anymore');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new UAParser();

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new UAParser();

        $result = $provider->parse('Googlebot/2.1 (+http://www.googlebot.com/bot.html)');
        $this->assertEquals([
            'browser' => [
                'name' => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'renderingEngine' => [
                'name' => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'operatingSystem' => [
                'name' => null,
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
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
            'bot' => [
                'isBot' => true,
                'name' => 'Googlebot',
                'type' => null,
            ],
        ], $result->toArray());

        // Test the raw result
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf('UAParser\Result\Client', $rawResult);
        $this->assertObjectHasAttribute('ua', $rawResult);
        $this->assertObjectHasAttribute('os', $rawResult);
        $this->assertObjectHasAttribute('device', $rawResult);
        $this->assertObjectHasAttribute('originalUserAgent', $rawResult);

        //ua
        $ua = $rawResult->ua;
        $this->assertInstanceOf('UAParser\Result\UserAgent', $ua);
        $this->assertObjectHasAttribute('major', $ua);
        $this->assertObjectHasAttribute('minor', $ua);
        $this->assertObjectHasAttribute('patch', $ua);
        $this->assertObjectHasAttribute('family', $ua);

        //os
        $os = $rawResult->os;
        $this->assertInstanceOf('UAParser\Result\OperatingSystem', $os);
        $this->assertObjectHasAttribute('major', $os);
        $this->assertObjectHasAttribute('minor', $os);
        $this->assertObjectHasAttribute('patch', $os);
        $this->assertObjectHasAttribute('patchMinor', $os);
        $this->assertObjectHasAttribute('family', $os);

        //os
        $device = $rawResult->device;
        $this->assertInstanceOf('UAParser\Result\Device', $device);
        $this->assertObjectHasAttribute('brand', $device);
        $this->assertObjectHasAttribute('model', $device);
        $this->assertObjectHasAttribute('family', $device);
    }

    public function testRealResultDevice()
    {
        $provider = new UAParser();

        $result = $provider->parse('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3');
        $this->assertEquals([
            'browser' => [
                'name' => 'Mobile Safari',
                'version' => [
                    'major' => 5,
                    'minor' => 1,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.1',
                ],
            ],
            'renderingEngine' => [
                'name' => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'operatingSystem' => [
                'name' => 'iOS',
                'version' => [
                    'major' => 5,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.0',
                ],
            ],
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
            'bot' => [
                'isBot' => null,
                'name' => null,
                'type' => null,
            ],
        ], $result->toArray());
    }

    private function getParser()
    {
        return new \UAParser\Parser(include 'tests/resources/uaparser/regexes.php');
    }
}
