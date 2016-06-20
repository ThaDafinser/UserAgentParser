<?php
namespace UserAgentParserTest\Integration\Provider\Http;

use UserAgentParser\Provider\Http\FiftyOneDegreesCom;

/**
 * @coversNothing
 */
class FiftyOneDegreesComTest extends AbstractHttpProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Your API key "invalid_api_key" is not valid for FiftyOneDegreesCom
     */
    public function testInvalidCredentials()
    {
        if (getenv('TRAVIS') === true) {
            $this->markTestSkipped('On travis we got currently an SSL problem');
        }

        $provider = new FiftyOneDegreesCom($this->getClient(), 'invalid_api_key');

        $result = $provider->parse('...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        if (! defined('CREDENTIALS_FIFTYONE_DEGREES_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new FiftyOneDegreesCom($this->getClient(), CREDENTIALS_FIFTYONE_DEGREES_COM_KEY);

        $result = $provider->parse('a');
    }

    public function testRealResultBot()
    {
        if (! defined('CREDENTIALS_FIFTYONE_DEGREES_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new FiftyOneDegreesCom($this->getClient(), CREDENTIALS_FIFTYONE_DEGREES_COM_KEY);

        $result = $provider->parse('Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0); 360Spider(compatible; HaosouSpider; http://www.haosou.com/help/help_3_2.html)');
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
        if (! defined('CREDENTIALS_FIFTYONE_DEGREES_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new FiftyOneDegreesCom($this->getClient(), CREDENTIALS_FIFTYONE_DEGREES_COM_KEY);

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
                'name'    => 'Webkit',
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
                'brand' => 'Apple',
                'type'  => 'SmartPhone',

                'isMobile' => true,
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

        $this->assertInstanceOf('stdClass', $rawResult);
        $this->assertCount(135, (array) $rawResult);

        $this->assertObjectHasAttribute('MatchMethod', $rawResult);
        $this->assertObjectHasAttribute('BrowserName', $rawResult);
        $this->assertObjectHasAttribute('BrowserVersion', $rawResult);

        $this->assertObjectHasAttribute('LayoutEngine', $rawResult);

        $this->assertObjectHasAttribute('PlatformName', $rawResult);
        $this->assertObjectHasAttribute('PlatformVersion', $rawResult);

        $this->assertObjectHasAttribute('HardwareVendor', $rawResult);
        $this->assertObjectHasAttribute('HardwareFamily', $rawResult);
        $this->assertObjectHasAttribute('DeviceType', $rawResult);
        $this->assertObjectHasAttribute('IsMobile', $rawResult);

        $this->assertObjectHasAttribute('IsCrawler', $rawResult);
    }
}
