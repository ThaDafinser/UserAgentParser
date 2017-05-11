<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use UserAgentParser\Provider\Mimmi20BrowserDetector;
require 'vendor/autoload.php';

$ua = 'test';
// $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3';
$ua = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
// $ua = 'Mozilla/5.0 (Linux; U; Android 3.0.1; en-us; HTC T9299+ For AT&T Build/GRJ22) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1';
$ua = 'FeedFetcher-Google; (+http://www.google.com/feedfetcher.html)';

$detector = new Mimmi20BrowserDetector();
$result = $detector->parse($ua);

var_dump($result);
exit();
