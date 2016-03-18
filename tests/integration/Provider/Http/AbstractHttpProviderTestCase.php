<?php
namespace UserAgentParserTest\Integration\Provider\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use UserAgentParserTest\Integration\Provider\AbstractProviderTestCase;

abstract class AbstractHttpProviderTestCase extends AbstractProviderTestCase
{
    private $client;

    public function setUp()
    {
        /*
         * move tests/credentials.php.dist to tests/credentials.php
         */
        if (! defined('CREDENTIALS_FILE_LOADED') && file_exists('tests/credentials.php')) {
            include 'tests/credentials.php';
        }

        /*
         * If you need an alternativ client to test the integration -> move test/client.php.dist to test/client.php and define your things!
         */
        if (file_exists('tests/client.php')) {
            $client = include 'tests/client.php';

            if ($client instanceof Client) {
                $this->client = $client;
            }
        }
    }

    /**
     *
     * @return Client
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $handler = new CurlHandler();
            $stack   = HandlerStack::create($handler);

            $this->client = new Client([
                'handler' => $stack,
                'timeout' => 3,

                'curl' => [
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                ],
            ]);
        }

        return $this->client;
    }
}
