<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\BrowscapPhp;

/**
 * @covers UserAgentParser\Provider\BrowscapPhp
 */
class BrowscapPhpTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new BrowscapPhp();

        $this->assertEquals('BrowscapPhp', $provider->getName());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $userAgent = 'not valid';

        $provider = new BrowscapPhp();
        $provider->parse($userAgent);
    }
}
