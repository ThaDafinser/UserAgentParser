<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\BrowscapPhp;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
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
        $cache = self::createMock('BrowscapPHP\Cache\BrowscapCache');
        $cache->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(''));

        $parser = self::createMock('BrowscapPHP\Browscap');
        $parser->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue($cache));
        $parser->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($result));

        return $parser;
    }

    public function testGetName()
    {
        $provider = new BrowscapPhp($this->getParser());

        $this->assertEquals('BrowscapPhp', $provider->getName());
    }

    public function testDetectionCapabilities()
    {
        $provider = new BrowscapPhp($this->getParser());

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
                'isTouch'  => true,
            ],

            'bot' => [
                'isBot' => true,
                'name'  => true,
                'type'  => false,
            ],
        ], $provider->getDetectionCapabilities());
    }
}
