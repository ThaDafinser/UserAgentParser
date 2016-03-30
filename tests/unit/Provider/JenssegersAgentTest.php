<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\JenssegersAgent;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\JenssegersAgent
 */
class JenssegersAgentTest extends AbstractProviderTestCase
{
    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser()
    {
        $parser = $this->getMock('Jenssegers\Agent\Agent', [], [], '', false);

        return $parser;
    }

    /**
     * @expectedException \UserAgentParser\Exception\PackageNotLoadedException
     */
    public function testPackageNotLoadedException()
    {
        $file     = 'vendor/jenssegers/agent/composer.json';
        $tempFile = 'vendor/jenssegers/agent/composer.json.tmp';

        rename($file, $tempFile);

        try {
            $provider = new JenssegersAgent();
        } catch (\Exception $ex) {
            // we need to catch the exception, since we need to rename the file again!
            rename($tempFile, $file);

            throw $ex;
        }

        rename($tempFile, $file);
    }

    public function testName()
    {
        $provider = new JenssegersAgent();

        $this->assertEquals('JenssegersAgent', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new JenssegersAgent();

        $this->assertEquals('https://github.com/jenssegers/agent', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new JenssegersAgent();

        $this->assertEquals('jenssegers/agent', $provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new JenssegersAgent();

        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new JenssegersAgent();

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new JenssegersAgent();

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
                'name'    => true,
                'version' => true,
            ],

            'device' => [
                'model'    => false,
                'brand'    => false,
                'type'     => false,
                'isMobile' => true,
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
        $provider = new JenssegersAgent();

        $this->assertInstanceOf('Jenssegers\Agent\Agent', $provider->getParser());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $parser = $this->getParser();

        $provider = new JenssegersAgent();

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
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(true));

        $parser->expects($this->any())
            ->method('robot')
            ->will($this->returnValue('Googlebot'));

        $provider = new JenssegersAgent();

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
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('browser')
            ->will($this->returnValue('Firefox'));
        $parser->expects($this->any())
            ->method('version')
            ->will($this->returnValue('3.2.1'));

        $provider = new JenssegersAgent();

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
                    'minor' => 2,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.2.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * OS only
     */
    public function testParseOs()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('platform')
            ->will($this->returnValue('Windows'));
        $parser->expects($this->any())
            ->method('version')
            ->will($this->returnValue('7.0.1'));

        $provider = new JenssegersAgent();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'Windows',
                'version' => [
                    'major' => 7,
                    'minor' => 0,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '7.0.1',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only
     */
    public function testDeviceOnly()
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isMobile')
            ->will($this->returnValue(true));

        $provider = new JenssegersAgent();

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => null,

                'isMobile' => true,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
