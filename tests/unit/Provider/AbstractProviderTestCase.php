<?php
namespace UserAgentParserTest\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\UserAgent;

abstract class AbstractProviderTestCase extends PHPUnit_Framework_TestCase
{
    protected $autoloadFunctions = [];

    protected $autoloadIsDisabled = false;

    public function assertProviderResult($result, array $expectedResult)
    {
        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);

        $model          = new UserAgent();
        $expectedResult = array_merge($model->toArray(), $expectedResult);

        $this->assertEquals($result->toArray(), $expectedResult);
    }

    protected function backupAutoload()
    {
        $this->autoloadFunctions = spl_autoload_functions();
    }

    protected function disableDefaultAutoload()
    {
        if ($this->autoloadIsDisabled === true) {
            return;
        }

        foreach ($this->autoloadFunctions as $function) {
            spl_autoload_unregister($function);
        }

        $this->autoloadIsDisabled = true;
    }

    protected function enableDefaultAutoload()
    {
        if ($this->autoloadIsDisabled !== true) {
            return;
        }

        foreach ($this->autoloadFunctions as $function) {
            spl_autoload_register($function);
        }
    }

    /**
     *
     * @return Client
     */
    protected function getClient(array $responseQueue = [])
    {
        $mock = new MockHandler($responseQueue);

        $handler = HandlerStack::create($mock);

        $client = new Client([
            'handler' => $handler,
        ]);

        return $client;
    }
}
