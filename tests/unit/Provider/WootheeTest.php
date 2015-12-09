<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\Woothee;

/**
 * @covers UserAgentParser\Provider\Woothee
 */
class WootheeTest extends AbstractProviderTestCase
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(array $returnValue = [])
    {
        $parser = $this->getMock('Woothee\Classifier', [], [], '', false);
        $parser->expects($this->any())
            ->method('parse')
            ->will($this->returnValue($returnValue));

        return $parser;
    }

    public function testPackageNotLoaded()
    {
        $this->backupAutoload();

        $autoloadFunction = function ($class) {
            if ($class == 'Woothee\Classifier') {
                $this->disableDefaultAutoload();
            } else {
                $this->enableDefaultAutoload();
            }
        };

        spl_autoload_register($autoloadFunction, true, true);

        try {
            $provider = new Woothee();
        } catch (\Exception $ex) {
        }

        $this->assertInstanceOf('UserAgentParser\Exception\PackageNotLoaded', $ex);

        spl_autoload_unregister($autoloadFunction);
        $this->enableDefaultAutoload();
    }

    public function testName()
    {
        $provider = new Woothee();

        $this->assertEquals('Woothee', $provider->getName());
    }

    public function testGetComposerPackageName()
    {
        $provider = new Woothee();

        $this->assertEquals('woothee/woothee', $provider->getComposerPackageName());
    }

    public function testVersion()
    {
        $provider = new Woothee();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testDetectionCapabilities()
    {
        $provider = new Woothee();

        $this->assertEquals([

            'browser' => [
                'name'    => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name'    => false,
                'version' => false,
            ],

            'operatingSystem' => [
                'name'    => false,
                'version' => false,
            ],

            'device' => [
                'model'    => false,
                'brand'    => false,
                'type'     => true,
                'isMobile' => false,
                'isTouch'  => false,
            ],

            'bot' => [
                'isBot' => true,
                'name'  => true,
                'type'  => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function testParser()
    {
        $provider = new Woothee();

        $this->assertInstanceOf('Woothee\Classifier', $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $parser = $this->getParser();

        $provider = new Woothee();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $parser = $this->getParser([
            'category' => \Woothee\DataSet::DATASET_CATEGORY_CRAWLER,
            'name'     => 'Googlebot',
        ]);

        $provider = new Woothee();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => 'Googlebot',
                'type'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only
     */
    public function testParseBrowser()
    {
        $parser = $this->getParser([
            'name'    => 'Firefox',
            'version' => '3.0.1',
        ]);

        $provider = new Woothee();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 0,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.0.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only
     */
    public function testParseDevice()
    {
        $parser = $this->getParser([
            'category' => \Woothee\DataSet::DATASET_CATEGORY_SMARTPHONE,
        ]);

        $provider = new Woothee();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'smartphone',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only
     */
    public function testParseDeviceMobilephone()
    {
        $parser = $this->getParser([
            'category' => \Woothee\DataSet::DATASET_CATEGORY_MOBILEPHONE,
            'name'     => \Woothee\DataSet::VALUE_UNKNOWN,
        ]);

        $provider = new Woothee();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],

            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'mobilephone',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
