<?php
namespace UserAgentParserTest;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\Version;

/**
 * @covers UserAgentParser\Model\Version
 */
class VersionTest extends PHPUnit_Framework_TestCase
{
    public function testMajorMinorPatch()
    {
        $version = new Version();

        $this->assertNull($version->getMajor());
        $this->assertNull($version->getMinor());
        $this->assertNull($version->getPatch());

        $version->setMajor(2);
        $version->setMinor(3);
        $version->setPatch(4);

        $this->assertEquals(2, $version->getMajor());
        $this->assertEquals(3, $version->getMinor());
        $this->assertEquals(4, $version->getPatch());
    }

    public function testComplete()
    {
        $version = new Version();

        $this->assertNull($version->getComplete());

        // null stays null
        $version->setComplete(null);
        $this->assertNull($version->getComplete());

        $version->setComplete('2.0.1');
        $this->assertEquals('2.0.1', $version->getComplete());
        $this->assertEquals(2, $version->getMajor());
        $this->assertEquals(0, $version->getMinor());
        $this->assertEquals(1, $version->getPatch());

        $version->setComplete('2.0');
        $this->assertEquals('2.0', $version->getComplete());
        $this->assertEquals(2, $version->getMajor());
        $this->assertEquals(0, $version->getMinor());
        $this->assertEquals(null, $version->getPatch());

        $version->setMajor(3);
        $this->assertEquals('3.0', $version->getComplete());
    }

    public function testToArray()
    {
        $version = new Version();

        $this->assertEquals([
            'major' => null,
            'minor' => null,
            'patch' => null,

            'complete' => null,
        ], $version->toArray());

        $version->setComplete('3.1.5');
        $this->assertEquals([
            'major' => 3,
            'minor' => 1,
            'patch' => 5,

            'complete' => '3.1.5',
        ], $version->toArray());
    }
}
