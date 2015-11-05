<?php
include 'bootstrap.php';

use BrowscapPHP\Exception;
use BrowscapPHP\Browscap;
use BrowscapPHP\Cache\BrowscapCache;
use WurflCache\Adapter\File;
use BrowscapPHP\Command;
use DeviceDetector\DeviceDetector;
use Doctrine\Common\Cache;

/*
 * BrowscapPHP
 */
echo 'BrowscapPHP' . PHP_EOL;

$cacheAdapter = new File(array(
    File::DIR => '.tmp/browscap'
));
$cache = new BrowscapCache($cacheAdapter);

$parser = new Browscap();
$parser->setCache($cache);
$parser->convertFile('vendor/browscap/browscap/build/php_browscap.ini');

/*
 * Piwik
 */
echo 'PiwikDeviceDetector' . PHP_EOL;

$dd = new DeviceDetector();
$dd->setCache(new Cache\PhpFileCache('.tmp/piwik'));
$dd->setUserAgent('test');
$dd->parse();
