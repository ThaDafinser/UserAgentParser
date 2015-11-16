<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\Wurfl;

/**
 * @covers UserAgentParser\Provider\Wurfl
 */
class WurflTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new Wurfl();

        $this->assertEquals('Wurfl', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new Wurfl();

        $this->assertEquals('mimmi20/wurfl', $provider->getComposerPackageName());
    }
}
