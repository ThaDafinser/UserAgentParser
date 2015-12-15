<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\YzalisUAParser;

/**
 * @coversNothing
 */
class YzalisUAParserTest extends AbstractProviderTestCase
{
    private function getParser()
    {
        return new \UAParser\UAParser('tests/resources/yzalis/regex.yml');
    }

    public function testMethodParse()
    {
        $provider = new YzalisUAParser($this->getParser());
        $parser   = $provider->getParser();

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('parse'), 'method parse() does not exist anymore');

        /*
         * test paramters
         */
        $method     = $class->getMethod('parse');
        $parameters = $method->getParameters();

        $this->assertEquals(2, count($parameters));

        /* @var $optionalPara \ReflectionParameter */
        $optionalPara = $parameters[1];

        $this->assertTrue($optionalPara->isOptional(), '2nd parameter of parse() is not optional anymore');
    }

    public function testRealResult()
    {
        $provider = new YzalisUAParser($this->getParser());
        $parser   = $provider->getParser();

        /* @var $result \UAParser\Result\Result */
        $result = $parser->parse('A real user agent...');

        $this->assertInstanceOf('UAParser\Result\Result', $result);

        $class = new \ReflectionClass($result);

        $this->assertTrue($class->hasMethod('getBrowser'), 'method getBrowser() does not exist anymore');
        $this->assertInstanceOf('UAParser\Result\BrowserResult', $result->getBrowser());

        $this->assertTrue($class->hasMethod('getRenderingEngine'), 'method getRenderingEngine() does not exist anymore');
        $this->assertInstanceOf('UAParser\Result\RenderingEngineResult', $result->getRenderingEngine());

        $this->assertTrue($class->hasMethod('getOperatingSystem'), 'method getOperatingSystem() does not exist anymore');
        $this->assertInstanceOf('UAParser\Result\OperatingSystemResult', $result->getOperatingSystem());

        $this->assertTrue($class->hasMethod('getDevice'), 'method getDevice() does not exist anymore');
        $this->assertInstanceOf('UAParser\Result\DeviceResult', $result->getDevice());
    }

    public function testClassBrowserResult()
    {
        $class = new \ReflectionClass('UAParser\Result\BrowserResult');

        $this->assertTrue($class->hasMethod('getFamily'), 'method getFamily() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersionString'), 'method getVersionString() does not exist anymore');
    }

    public function testClassEngineResult()
    {
        $class = new \ReflectionClass('UAParser\Result\RenderingEngineResult');

        $this->assertTrue($class->hasMethod('getFamily'), 'method getFamily() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersion'), 'method getVersion() does not exist anymore');
    }

    public function testClassOsResult()
    {
        $class = new \ReflectionClass('UAParser\Result\OperatingSystemResult');

        $this->assertTrue($class->hasMethod('getFamily'), 'method getFamily() does not exist anymore');
        $this->assertTrue($class->hasMethod('getMajor'), 'method getMajor() does not exist anymore');
        $this->assertTrue($class->hasMethod('getMinor'), 'method getMinor() does not exist anymore');
        $this->assertTrue($class->hasMethod('getPatch'), 'method getPatch() does not exist anymore');
    }

    public function testClassDeviceResult()
    {
        $class = new \ReflectionClass('UAParser\Result\DeviceResult');

        $this->assertTrue($class->hasMethod('getModel'), 'method getModel() does not exist anymore');
        $this->assertTrue($class->hasMethod('getConstructor'), 'method getConstructor() does not exist anymore');
        $this->assertTrue($class->hasMethod('getType'), 'method getType() does not exist anymore');
    }
}
