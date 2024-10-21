<?php

namespace UserAgentParserTest\Unit\Provider;

use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use UserAgentParser\Provider\BrowscapPhp;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers \UserAgentParser\Provider\BrowscapPhp
 *
 * @internal
 */
class BrowscapPhpTest extends AbstractProviderTestCase
{
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
                'name' => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name' => false,
                'version' => false,
            ],

            'operatingSystem' => [
                'name' => true,
                'version' => false,
            ],

            'device' => [
                'model' => false,
                'brand' => false,
                'type' => true,
                'isMobile' => true,
                'isTouch' => true,
            ],

            'bot' => [
                'isBot' => true,
                'name' => true,
                'type' => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(stdClass $result = null)
    {
        $cache = self::createMock('BrowscapPHP\Cache\BrowscapCache');
        $cache->expects($this->any())
            ->method('getType')
            ->willReturn('');

        $parser = self::createMock('BrowscapPHP\Browscap');
        $parser->expects($this->any())
            ->method('getCache')
            ->willReturn($cache);
        $parser->expects($this->any())
            ->method('getBrowser')
            ->willReturn($result);

        return $parser;
    }
}
