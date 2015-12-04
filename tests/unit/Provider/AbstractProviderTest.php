<?php
namespace UserAgentParserTest\Provider;

/**
 * @covers UserAgentParser\Provider\AbstractProvider
 */
class AbstractProviderTest extends AbstractProviderTestCase
{
    public function testVersionNull()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        // no package name
        $this->assertNull($provider->getVersion());

        // no composer.lock found
        $cwdir = getcwd();
        chdir('tests');

        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');
        $provider->expects($this->any())
            ->method('getComposerPackageName')
            ->will($this->returnValue('something/other'));

        $this->assertNull($provider->getVersion());
        chdir($cwdir);

        // locked file
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');
        $provider->expects($this->any())
            ->method('getComposerPackageName')
            ->will($this->returnValue('something/other'));

        $fp = fopen('composer.lock', 'r');
        flock($fp, LOCK_EX);
        $this->assertNull($provider->getVersion());
        flock($fp, LOCK_UN);

        // no package match
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');
        $provider->expects($this->any())
            ->method('getComposerPackageName')
            ->will($this->returnValue('something/other'));

        $this->assertNull($provider->getVersion());
    }

    public function testVersion()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');
        $provider->expects($this->any())
            ->method('getComposerPackageName')
            ->will($this->returnValue('browscap/browscap-php'));

        // match
        $this->assertInternalType('string', $provider->getVersion());

        // cached
        $this->assertInternalType('string', $provider->getVersion());
    }

    public function testDetectionCapabilities()
    {
        $provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');

        $this->assertInternalType('array', $provider->getDetectionCapabilities());
        $this->assertCount(5, $provider->getDetectionCapabilities());
        $this->assertFalse($provider->getDetectionCapabilities()['browser']['name']);
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
            'default value',
        ]);

        $method = $reflection->getMethod('isRealResult');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($provider, 'default value'));

        $this->assertTrue($method->invoke($provider, 'default other'));

        $this->assertFalse($method->invoke($provider, 'default other', [
            'default',
            'default other',
        ]));
    }
}
