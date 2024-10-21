<?php

namespace UserAgentParserTest\Unit\Provider;

use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use UserAgentParser\Provider\BrowscapFull;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers \UserAgentParser\Provider\BrowscapFull
 *
 * @internal
 */
class BrowscapFullTest extends AbstractProviderTestCase
{
    public function testGetName()
    {
        $provider = new BrowscapFull($this->getParser());

        $this->assertEquals('BrowscapFull', $provider->getName());
    }

    public function testDetectionCapabilities()
    {
        $provider = new BrowscapFull($this->getParser());

        $this->assertEquals([
            'browser' => [
                'name' => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name' => true,
                'version' => true,
            ],

            'operatingSystem' => [
                'name' => true,
                'version' => true,
            ],

            'device' => [
                'model' => true,
                'brand' => true,
                'type' => true,
                'isMobile' => true,
                'isTouch' => true,
            ],

            'bot' => [
                'isBot' => true,
                'name' => true,
                'type' => true,
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
            ->willReturn('FULL');

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
