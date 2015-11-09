<?php
require 'vendor/autoload.php';

use UserAgentParser\Provider;

$userAgents = [
    '',
    'Mozilla/5 (X11; Linux x86_64) AppleWebKit/537.4 (KHTML like Gecko) Arch Linux Firefox/23.0 Xfce',
    
    'Mozilla/5.0 (compatible; Genieo/1.0 http://www.genieo.com/webfilter.html)',
    'Mozilla/5.0 (Linux; U; Android 4.0.4; en-gb; GT-I9300 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
    'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5',
    'Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_5 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5',
    'Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5',
    'Mozilla/5.0 (compatible; special_archiver/3.1.1 +http://www.archive.org/details/archive.org_bot)',
    'Googlebot/2.1 (http://www.googlebot.com/bot.html)',
    'FeedBurner/1.0 (http://www.FeedBurner.com)',
    'Pinterest/0.2 (+http://www.pinterest.com/)',
    'RssBandit/1.9.0.1002 (.NET CLR 2.0.50727.7512; WinNT 6.2.9200.0; http://www.rssbandit.org)'
];

$userAgent = $userAgents[0];

// /*
// * BrowscapPhp
// */
// $browscapFull = new Provider\BrowscapPhp();
// $browscapFull->setCachePath('.tmp/browscap_full');
// $browscapFull->setCachePath('.tmp/browscap_lite');

// $result = $browscapFull->parse($userAgent);
// var_dump($result->toArray());

// /*
//  * DonatjUAParser
//  */
// $dd = new Provider\DonatjUAParser();
// $result = $dd->parse($userAgent);
// var_dump($result->toArray());

// /*
//  * PiwikDeviceDetector
//  */
// $dd = new Provider\PiwikDeviceDetector();
// $result = $dd->parse($userAgent);
// var_dump($result->toArray());

// /*
//  * UAParser
//  */
// $dd = new Provider\UAParser();
// $result = $dd->parse($userAgent);
// var_dump($result->toArray());

// /*
//  * WhichBrowser
//  */
// $dd = new Provider\WhichBrowser();
// $result = $dd->parse($userAgent);
// var_dump($result->toArray());

// /*
//  * Woothee
//  */
// $dd = new Provider\Woothee();
// $result = $dd->parse($userAgent);
// var_dump($result->toArray());

/*
 * YzalisUAParser
 */
$dd = new Provider\YzalisUAParser();
$result = $dd->parse($userAgent);
var_dump($result->toArray());
