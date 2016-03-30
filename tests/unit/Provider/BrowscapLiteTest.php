<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\BrowscapLite;
use UserAgentParser\Provider\BrowscapPhp;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\BrowscapLite
 */
class BrowscapLiteTest extends AbstractProviderTestCase
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(\stdClass $result = null)
    {
        $cache = $this->getMock('BrowscapPHP\Cache\BrowscapCache', [], [], '', false);
        $cache->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('LITE'));

        $parser = $this->getMock('BrowscapPHP\Browscap');
        $parser->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue($cache));
        $parser->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($result));

        return $parser;
    }

    public function testName()
    {
        $provider = new BrowscapLite($this->getParser());

        $this->assertEquals('BrowscapLite', $provider->getName());
    }

    public function testDetectionCapabilities()
    {
        $provider = new BrowscapLite($this->getParser());

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
                'version' => false,
            ],

            'device' => [
                'model'    => false,
                'brand'    => false,
                'type'     => true,
                'isMobile' => true,
                'isTouch'  => false,
            ],

            'bot' => [
                'isBot' => false,
                'name'  => false,
                'type'  => false,
            ],
        ], $provider->getDetectionCapabilities());
    }
}
