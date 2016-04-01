<?php
namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\Chain;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *         
 * @covers UserAgentParser\Provider\Chain
 */
class ChainTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    public function setUp()
    {
        parent::setUp();

        $this->provider = $this->getMockForAbstractClass('UserAgentParser\Provider\AbstractProvider');
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->provider = null;
    }

    public function testProvider()
    {
        $chain = new Chain();

        $this->assertInternalType('array', $chain->getProviders());
        $this->assertCount(0, $chain->getProviders());

        $chain = new Chain([
            $this->provider,
        ]);

        $this->assertInternalType('array', $chain->getProviders());
        $this->assertCount(1, $chain->getProviders());
        $this->assertSame([
            $this->provider,
        ], $chain->getProviders());
    }

    public function testGetName()
    {
        $chain = new Chain();

        $this->assertEquals('Chain', $chain->getName());
    }

    public function testGetHomepage()
    {
        $provider = new Chain();

        $this->assertNull($provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new Chain();

        $this->assertNull($provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new Chain();

        $this->assertNull($provider->getVersion());
    }

    public function testUpdateDate()
    {
        $provider = new Chain();

        $this->assertNull($provider->getUpdateDate());
    }

    public function testDetectionCapabilities()
    {
        $provider = new Chain();

        $this->assertEquals([

            'browser' => [
                'name'    => false,
                'version' => false,
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
                'type'     => false,
                'isMobile' => false,
                'isTouch'  => false,
            ],

            'bot' => [
                'isBot' => false,
                'name'  => false,
                'type'  => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function testIsRealResult()
    {
        $provider = new Chain();

        /*
         * general
         */
        $this->assertIsRealResult($provider, true, 'something UNKNOWN');
    }

    /**
     * @todo should throw another exception! since no provider was provided!
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoProviderNoResultFoundException()
    {
        $chain = new Chain();

        $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

        $chain->parse($userAgent);
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $provider = $this->provider;
        $provider->expects($this->any())
            ->method('parse')
            ->will($this->throwException(new \UserAgentParser\Exception\NoResultFoundException()));

        $chain = new Chain([
            $provider,
        ]);

        $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

        $chain->parse($userAgent);
    }

    public function testParseWithProviderAndValidResult()
    {
        $resultMock = $this->getMock('UserAgentParser\Model\UserAgent');

        $provider = $this->provider;
        $provider->expects($this->any())
            ->method('parse')
            ->will($this->returnValue($resultMock));

        $chain = new Chain([
            $provider,
        ]);

        $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

        $this->assertSame($resultMock, $chain->parse($userAgent));
    }
}
