<?php
namespace UserAgentParserTest\Provider;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\UserAgent;

abstract class AbstractProviderTestCase extends PHPUnit_Framework_TestCase
{
    public function assertProviderResult($result, array $expectedResult)
    {
        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);

        $model          = new UserAgent();
        $expectedResult = array_merge($model->toArray(), $expectedResult);

        $this->assertEquals($result->toArray(), $expectedResult);
    }
}
