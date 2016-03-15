<?php
namespace UserAgentParserTest\Integration\Provider;

use BrowscapPHP\Browscap;
use BrowscapPHP\Helper\IniLoader;
use UserAgentParser\Provider\BrowscapLite;

/**
 * @coversNothing
 */
class BrowscapLiteTest extends AbstractProviderTestCase
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

    private function getParserWithColdCache($type)
    {
        $filename = 'php_browscap.ini';
        if ($type != '') {
            $filename = $type . '_' . $filename;
        }

        $loader = new IniLoader();
        $loader->setLocalFile('tests/resources/browscap/' . $filename);

        $cache = new \WurflCache\Adapter\Memory();

        $browscap = new Browscap();
        $browscap->setCache($cache);
        $browscap->setLoader($loader);

        return $browscap;
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundWithWarmCache()
    {
        $provider = new BrowscapLite($this->getParserWithWarmCache('lite'));

        $result = $provider->parse('...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\InvalidArgumentException
     * @expectedExceptionMessage You need to warm-up the cache first to use this provider
     */
    public function testNoResultFoundWithColdCache()
    {
        $provider = new BrowscapLite($this->getParserWithColdCache('lite'));

        $result = $provider->parse('...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\InvalidArgumentException
     * @expectedExceptionMessage You need to warm-up the cache first to use this provider
     */
    public function testNoResultFoundWithColdCacheStillAfterGetBrowser()
    {
        $parser = $this->getParserWithColdCache('lite');

        $result = $parser->getBrowser('something');

        $provider = new BrowscapLite($parser);

        $result = $provider->parse('...');
    }

    public function testRealResultDevice()
    {
        $provider = new BrowscapLite($this->getParserWithWarmCache('lite'));

        $result = $provider->parse('Mozilla/5.0 (SMART-TV; X11; Linux armv7l) AppleWebkit/537.42 (KHTML, like Gecko) Chromium/48.0.1349.2 Chrome/25.0.1349.2 Safari/537.42');

        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);
        $this->assertEquals('Chromium', $result->getBrowser()
            ->getName());
        $this->assertEquals('48.0', $result->getBrowser()
            ->getVersion()
            ->getComplete());

        $this->assertEquals(null, $result->getRenderingEngine()
            ->getName());
        $this->assertEquals(null, $result->getRenderingEngine()
            ->getVersion()
            ->getComplete());

        $this->assertEquals('Linux', $result->getOperatingSystem()
            ->getName());
        $this->assertEquals(null, $result->getOperatingSystem()
            ->getVersion()
            ->getComplete());

        $this->assertEquals(null, $result->getDevice()
            ->getBrand());
        $this->assertEquals(null, $result->getDevice()
            ->getModel());
        $this->assertEquals('TV Device', $result->getDevice()
            ->getType());
    }
}
