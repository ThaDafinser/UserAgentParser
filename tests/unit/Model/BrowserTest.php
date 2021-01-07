<?php

namespace UserAgentParserTest;

use PHPUnit\Framework\TestCase;
use UserAgentParser\Model\Browser;
use UserAgentParser\Model\Version;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers \UserAgentParser\Model\Browser
 *
 * @internal
 */
class BrowserTest extends TestCase
{
    public function testName()
    {
        $browser = new Browser();

        $this->assertNull($browser->getName());

        $name = 'Firefox';
        $browser->setName($name);
        $this->assertEquals($name, $browser->getName());
    }

    public function testVersion()
    {
        $browser = new Browser();

        $this->assertInstanceOf('UserAgentParser\Model\Version', $browser->getVersion());

        $version = new Version();
        $browser->setVersion($version);
        $this->assertSame($version, $browser->getVersion());
    }

    public function testToArray()
    {
        $browser = new Browser();

        $this->assertEquals([
            'name' => null,
            'version' => $browser->getVersion()
                ->toArray(),
        ], $browser->toArray());

        $browser->setName('Chrome');
        $this->assertEquals([
            'name' => 'Chrome',
            'version' => $browser->getVersion()
                ->toArray(),
        ], $browser->toArray());
    }
}
