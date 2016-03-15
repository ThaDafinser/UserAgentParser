<?php
namespace UserAgentParserTest\Integration\Provider;

use BrowscapPHP\Browscap;
use UserAgentParser\Provider\BrowscapFull;

/**
 * @coversNothing
 */
class BrowscapFullTest extends AbstractProviderTestCase
{
    private function getParserWithWarmCache($type)
    {
        $filename = 'php_browscap.ini';
        if ($type != '') {
            $filename = $type . '_' . $filename;
        }

        $cache = new \WurflCache\Adapter\Memory();

        $browscap = new Browscap();
        $browscap->setCache($cache);
        $browscap->convertFile('tests/resources/browscap/' . $filename);

        return $browscap;
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundWithWarmCache()
    {
        $provider = new BrowscapFull($this->getParserWithWarmCache('full'));

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new BrowscapFull($this->getParserWithWarmCache('full'));

        $result = $provider->parse('Mozilla/2.0 (compatible; Ask Jeeves)');

        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);
        $this->assertTrue($result->getBot()
            ->getIsBot());
        $this->assertEquals('AskJeeves', $result->getBot()
            ->getName());
        $this->assertEquals('Bot/Crawler', $result->getBot()
            ->getType());

        $rawResult = $result->getProviderResultRaw();
        $this->assertInstanceOf('stdClass', $rawResult);
    }

    public function testRealResultDevice()
    {
        $provider = new BrowscapFull($this->getParserWithWarmCache('full'));

        $result = $provider->parse('Mozilla/5.0 (SMART-TV; X11; Linux armv7l) AppleWebkit/537.42 (KHTML, like Gecko) Chromium/48.0.1349.2 Chrome/25.0.1349.2 Safari/537.42');

        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);
        $this->assertEquals('Chromium', $result->getBrowser()
            ->getName());
        $this->assertEquals('48.0', $result->getBrowser()
            ->getVersion()
            ->getComplete());

        $this->assertEquals('Blink', $result->getRenderingEngine()
            ->getName());
        $this->assertEquals(null, $result->getRenderingEngine()
            ->getVersion()
            ->getComplete());

        $this->assertEquals('Linux', $result->getOperatingSystem()
            ->getName());
        $this->assertEquals(null, $result->getOperatingSystem()
            ->getVersion()
            ->getComplete());

        $this->assertEquals('Samsung', $result->getDevice()
            ->getBrand());
        $this->assertEquals('Smart TV', $result->getDevice()
            ->getModel());
        $this->assertEquals('TV Device', $result->getDevice()
            ->getType());
    }
}
