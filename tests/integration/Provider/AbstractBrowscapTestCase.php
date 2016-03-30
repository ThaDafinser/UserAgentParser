<?php
namespace UserAgentParserTest\Integration\Provider;

use BrowscapPHP\Browscap;
use BrowscapPHP\Helper\IniLoader;

/**
 * 
 * 
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class AbstractBrowscapTestCase extends AbstractProviderTestCase
{
    protected function getParserWithWarmCache($type)
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

    protected function getParserWithColdCache($type)
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
}
