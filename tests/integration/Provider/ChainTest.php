<?php
namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\Chain;
use UserAgentParser\Provider\PiwikDeviceDetector;
use UserAgentParser\Provider\WhichBrowser;
use UserAgentParser\Provider\Zsxsoft;

/**
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 * @coversNothing
 */
class ChainTest extends AbstractProviderTestCase
{
    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundSingleProvider()
    {
        $provider = new Chain([
            new WhichBrowser(),
        ]);

        $provider->parse('...');
    }

    /**
     * Also with multiple providers the excepction must be thrown
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundMultipleProviders()
    {
        $provider = new Chain([
            new WhichBrowser(),
            new Zsxsoft(),
            new PiwikDeviceDetector(),
        ]);

        $provider->parse('...');
    }

    public function testRealResultSingleProvider()
    {
        $provider = new Chain([
            new WhichBrowser(),
        ]);

        $result = $provider->parse('Googlebot/2.1 (+http://www.google.com/bot.html)');

        $this->assertTrue($result->getBot()
            ->getIsBot());
    }

    /**
     * This test makes sure, that the chain provider go to the next provider when no result is found
     */
    public function testRealResultTwoProviderSecondUsed()
    {
        $provider = new Chain([
            new Zsxsoft(),
            new PiwikDeviceDetector(),
        ]);

        $result = $provider->parse('Googlebot/2.1 (+http://www.google.com/bot.html)');

        // Zsxsoft cannot detect bots, so true here is not possible
        $this->assertTrue($result->getBot()
            ->getIsBot());
    }

    /**
     * This test makes sure, that the chain provider stops when a result is found
     */
    public function testRealResultThreeProviderSecondUsed()
    {
        $provider = new Chain([
            new Zsxsoft(),
            new PiwikDeviceDetector(),
            new WhichBrowser(),
        ]);

        $result = $provider->parse('Googlebot/2.1 (+http://www.google.com/bot.html)');

        // Zsxsoft cannot detect bots!
        $this->assertTrue($result->getBot()
            ->getIsBot());
        // WhichBrowser cannot detect the bot type
        $this->assertEquals('Search bot', $result->getBot()
            ->getType());
    }
}
