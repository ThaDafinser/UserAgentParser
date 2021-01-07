<?php

namespace UserAgentParserTest\Integration\Provider\Http;

use UserAgentParser\Provider\Http\UdgerCom;

/**
 * @coversNothing
 *
 * @internal
 */
class UdgerComTest extends AbstractHttpProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Your API key "invalid_api_key" is not valid for UdgerCom
     */
    public function testInvalidCredentials()
    {
        $provider = new UdgerCom($this->getClient(), 'invalid_api_key');

        $result = $provider->parse('...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFound()
    {
        if (!\defined('CREDENTIALS_UDGER_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $this->markTestIncomplete('Currently i have no valid API key to create more integration tests');

        $provider = new UdgerCom($this->getClient(), CREDENTIALS_UDGER_COM_KEY);

        $result = $provider->parse('...');
    }
}
