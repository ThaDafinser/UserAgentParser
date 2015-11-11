<?php
namespace UserAgentParserTest\Provider;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\UserAgent;

abstract class AbstractProviderTestCase extends PHPUnit_Framework_TestCase
{
    public function assertProviderResult($result, $expectedResult)
    {
        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);

        $defaultResult = new UserAgent();

        $expectedResult = array_merge($defaultResult->toArray(), $expectedResult);

        $this->assertEquals($result->toArray(), $expectedResult);
    }
}
