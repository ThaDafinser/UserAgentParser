<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\DonatjUAParser;

/**
 * @coversNothing
 */
class DonatjUAParserTest extends AbstractProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        $provider = new DonatjUAParser();

        $result = $provider->parse('...');
    }

    public function testRealResult()
    {
        $provider = new DonatjUAParser();

        $result = $provider->parse('Mozilla/5.0 (X11; U; CrOS i686 0.9.128; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.339');

        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);
        $this->assertEquals('Chrome', $result->getBrowser()->getName());
        $this->assertEquals('8.0.552.339', $result->getBrowser()->getVersion()->getComplete());

        $rawResult = $result->getProviderResultRaw();

        $this->assertInternalType('array', $rawResult);
        $this->assertArrayHasKey('platform', $rawResult);
        $this->assertArrayHasKey('browser', $rawResult);
        $this->assertArrayHasKey('version', $rawResult);

        $this->assertEquals('Chrome OS', $rawResult['platform']);
        $this->assertEquals('Chrome', $rawResult['browser']);
        $this->assertEquals('8.0.552.339', $rawResult['version']);
    }
}
