<?php

namespace UserAgentParserTest;

use PHPUnit\Framework\TestCase;
use UserAgentParser\Model\RenderingEngine;
use UserAgentParser\Model\Version;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers \UserAgentParser\Model\RenderingEngine
 *
 * @internal
 */
class RenderingEngineTest extends TestCase
{
    public function testName()
    {
        $engine = new RenderingEngine();

        $this->assertNull($engine->getName());

        $name = 'Webkit';
        $engine->setName($name);
        $this->assertEquals($name, $engine->getName());
    }

    public function testVersion()
    {
        $engine = new RenderingEngine();

        $this->assertInstanceOf('UserAgentParser\Model\Version', $engine->getVersion());

        $version = new Version();
        $engine->setVersion($version);
        $this->assertSame($version, $engine->getVersion());
    }

    public function testToArray()
    {
        $engine = new RenderingEngine();

        $this->assertEquals([
            'name' => null,
            'version' => $engine->getVersion()
                ->toArray(),
        ], $engine->toArray());

        $engine->setName('Trident');
        $this->assertEquals([
            'name' => 'Trident',
            'version' => $engine->getVersion()
                ->toArray(),
        ], $engine->toArray());
    }
}
