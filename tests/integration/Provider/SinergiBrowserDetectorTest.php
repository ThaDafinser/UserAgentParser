<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\SinergiBrowserDetector;

/**
 * @coversNothing
 */
class SinergiBrowserDetectorTest extends AbstractProviderTestCase
{
    public function testBrowserParser()
    {
        $provider = new SinergiBrowserDetector();

        $parser = $provider->getBrowserParser('something');

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersion'), 'method getVersion() does not exist anymore');
        $this->assertTrue($class->hasMethod('isRobot'), 'method isRobot() does not exist anymore');
    }

    public function testOsParser()
    {
        $provider = new SinergiBrowserDetector();

        $parser = $provider->getOperatingSystemParser('something');

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersion'), 'method getVersion() does not exist anymore');
        $this->assertTrue($class->hasMethod('isMobile'), 'method isMobile() does not exist anymore');
    }

    public function testDeviceParser()
    {
        $provider = new SinergiBrowserDetector();

        $parser = $provider->getDeviceParser('something');

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new SinergiBrowserDetector();

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new SinergiBrowserDetector();

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

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertInternalType('array', $rawResult);
        $this->assertArrayHasKey('browser', $rawResult);
        $this->assertArrayHasKey('operatingSystem', $rawResult);
        $this->assertArrayHasKey('device', $rawResult);

        $this->assertInstanceOf('Sinergi\BrowserDetector\Browser', $rawResult['browser']);
        $this->assertObjectHasAttribute('name', $rawResult['browser']);
        $this->assertObjectHasAttribute('version', $rawResult['browser']);
        $this->assertObjectHasAttribute('isRobot', $rawResult['browser']);

        $this->assertInstanceOf('Sinergi\BrowserDetector\Os', $rawResult['operatingSystem']);
        $this->assertObjectHasAttribute('name', $rawResult['operatingSystem']);
        $this->assertObjectHasAttribute('version', $rawResult['operatingSystem']);
        $this->assertObjectHasAttribute('isMobile', $rawResult['operatingSystem']);

        $this->assertInstanceOf('Sinergi\BrowserDetector\Device', $rawResult['device']);
        $this->assertObjectHasAttribute('name', $rawResult['device']);
    }

    public function testRealResultDevice()
    {
        $provider = new SinergiBrowserDetector();

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
                'model' => 'iPhone',
                'brand' => null,
                'type'  => null,

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
