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

        $result = $provider->parse('Mozilla/5.0 (Nintendo 3DS; U; ; en) Version/1.7552.EU');

        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);

        $rawResult = $result->getProviderResultRaw();

        $this->assertInternalType('array', $rawResult);
        $this->assertArrayHasKey('platform', $rawResult);
        $this->assertArrayHasKey('browser', $rawResult);
        $this->assertArrayHasKey('version', $rawResult);
    }
}
