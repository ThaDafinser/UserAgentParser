<?php

namespace UserAgentParserTest\Unit\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use UserAgentParser\Model\UserAgent;
use UserAgentParser\Provider\AbstractProvider;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
abstract class AbstractProviderTestCase extends TestCase
{
    protected function assertProviderResult($result, array $expectedResult)
    {
        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);

        $model = new UserAgent();
        $expectedResult = array_merge($model->toArray(), $expectedResult);

        $this->assertEquals($result->toArray(), $expectedResult);
    }

    protected function assertIsRealResult(AbstractProvider $provider, $expected, $value, $group = null, $part = null)
    {
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('isRealResult');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($provider, $value, $group, $part), $value);
    }

    /**
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
