<?php
namespace UserAgentParserTest\Unit\Provider;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers UserAgentParser\Provider\AbstractProvider
 */
class AbstractProviderTest extends AbstractProviderTestCase
{
    public function testGetName()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $this->assertNull($provider->getName());

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($provider, 'MyName');

        $this->assertEquals('MyName', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $this->assertNull($provider->getHomepage());

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('homepage');
        $property->setAccessible(true);
        $property->setValue($provider, 'https://github.com/vendor/package');

        $this->assertEquals('https://github.com/vendor/package', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $this->assertNull($provider->getPackageName());

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('packageName');
        $property->setAccessible(true);
        $property->setValue($provider, 'vendor/package');

        $this->assertEquals('vendor/package', $provider->getPackageName());
    }

    public function testVersionNull()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        // no package name
        $this->assertNull($provider->getVersion());

        // no package match
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('packageName');
        $property->setAccessible(true);
        $property->setValue($provider, 'vendor/package');

        $this->assertNull($provider->getVersion());
    }

    public function testVersion()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('packageName');
        $property->setAccessible(true);
        $property->setValue($provider, 'piwik/device-detector');

        // match
        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testUpdateDateNull()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        // no package name
        $this->assertNull($provider->getUpdateDate());

        // no package match
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('packageName');
        $property->setAccessible(true);
        $property->setValue($provider, 'vendor/package');

        $this->assertNull($provider->getUpdateDate());
    }

    public function testUpdateDate()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('packageName');
        $property->setAccessible(true);
        $property->setValue($provider, 'piwik/device-detector');

        // match
        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $this->assertInternalType('array', $provider->getDetectionCapabilities());
        $this->assertCount(5, $provider->getDetectionCapabilities());
        $this->assertFalse($provider->getDetectionCapabilities()['browser']['name']);
    }

    public function testCheckIfInstalled()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('packageName');
        $property->setAccessible(true);
        $property->setValue($provider, 'thadafinser/user-agent-parser');

        $reflection = new \ReflectionClass($provider);
        $method     = $reflection->getMethod('checkIfInstalled');
        $method->setAccessible(true);

        // no return, just no exception expected
        $method->invoke($provider);
    }

    /**
     * @expectedException \UserAgentParser\Exception\PackageNotLoadedException
     */
    public function testCheckIfInstalledException()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);
        $property   = $reflection->getProperty('packageName');
        $property->setAccessible(true);
        $property->setValue($provider, 'vendor/package');

        $reflection = new \ReflectionClass($provider);
        $method     = $reflection->getMethod('checkIfInstalled');
        $method->setAccessible(true);

        $method->invoke($provider);
    }

    public function testIsRealResult()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);
        $method     = $reflection->getMethod('isRealResult');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($provider, ''));
        $this->assertFalse($method->invoke($provider, null));

        $this->assertTrue($method->invoke($provider, 'some value'));
    }

    public function testIsRealResultWithDefaultValues()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('defaultValues');
        $property->setAccessible(true);
        $property->setValue($provider, [
            'general' => [
                '/^default value$/i',
            ],

            'bot' => [
                'name' => [
                    '/^default other$/i',
                ],
            ],
        ]);

        $method = $reflection->getMethod('isRealResult');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($provider, 'default value'));

        $this->assertTrue($method->invoke($provider, 'default other'));

        $this->assertFalse($method->invoke($provider, 'default other', 'bot', 'name'));
    }

    public function testGetRealResult()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);
        $method     = $reflection->getMethod('getRealResult');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($provider, ''));
        $this->assertNull($method->invoke($provider, null));

        $this->assertEquals('some value', $method->invoke($provider, 'some value'));
    }

    public function testGetRealResultWithDefaultValues()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('defaultValues');
        $property->setAccessible(true);
        $property->setValue($provider, [
            'general' => [
                '/^default value$/i',
            ],

            'bot' => [
                'name' => [
                    '/^default other$/i',
                ],
            ],
        ]);

        $method = $reflection->getMethod('getRealResult');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($provider, 'default value'));

        $this->assertEquals('default other', $method->invoke($provider, 'default other'));

        $this->assertNull($method->invoke($provider, 'default other', 'bot', 'name'));
    }
}
