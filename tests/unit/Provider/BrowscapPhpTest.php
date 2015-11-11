<?php
namespace UserAgentParserTest\Provider;

use UserAgentParser\Provider\BrowscapPhp;
use WurflCache\Adapter\File;

/**
 * @covers UserAgentParser\Provider\BrowscapPhp
 */
class BrowscapPhpTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new BrowscapPhp();

        $this->assertEquals('BrowscapPhp', $provider->getName());
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $userAgent = 'not valid';

        $provider = new BrowscapPhp();
        $provider->parse($userAgent);
    }

    public function testFirst()
    {
        /*
         * Init cache
         */
        $cache = new File([
            File::DIR => '.tmp/browscap_test',
        ]);

        $parser = new \BrowscapPHP\Browscap();
        $parser->setCache($cache);
        $parser->convertFile('tests/fixtures/browscap.ini');

        $userAgent = 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.0.1) Gecko/20020912';

        $provider = new BrowscapPhp();
        $provider->setCache($cache);

        $result =  $provider->parse($userAgent);

        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);
    }
}
