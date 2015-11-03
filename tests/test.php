<?php
set_time_limit(- 1);
chdir(dirname(__DIR__));

require 'vendor/autoload.php';

use UserAgentParser\Provider;
use UserAgentParser\GetMatrix;

/*
 * Source
 */
// $userAgents = include 'data/userAgents2.php';

/*
 * Mixed
 */
$userAgents = [
    // bots.yml
//     'Mozilla/5.0 (compatible; archive.org_bot; Wayback Machine Live Record; +http://archive.org/details/archive.org_bot)',
//     'Backlink-Ceck.de (+http://www.backlink-check.de/bot.html)',
//     'Pinterest/0.2 (+http://www.pinterest.com/)',
    
//     // camera.yml
//     'Mozilla/5.0 (Linux; Android 4.3; EK-GC200 Build/JSS15J) AppleWebKit/537.36 (KHTML like Gecko) Chrome/35.0.1916.141 Mobile Safari/537.36',
    
//     // car_browser.yml
//     'Mozilla/5.0 (X11; u; Linux; C) AppleWebKit /533.3 (Khtml, like Gheko) QtCarBrowser Safari/533.3',
    
//     // console.yml
//     'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; Xbox)',
//     'Mozilla/5.0 (PlayStation 4 1.52) AppleWebKit/536.26 (KHTML, like Gecko)',
    
    // desktop.yml
    'Mozilla/5.0 ArchLinux (X11; U; Linux x86_64; en-US) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.100 Safari/534.30',
    'Mozilla/5.0 (X11; U; Linux i686; fr-fr) AppleWebKit/531.2+ (KHTML, like Gecko) Version/5.0 Safari/531.2+ Debian/squeeze (2.30.6-1) Epiphany/2.30.6',
    'NCSA_Mosaic/2.7b5 (X11;Linux 2.6.7 i686) libwww/2.12 modified',
    'Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/125.5.7 (KHTML, like Gecko) SunriseBrowser/0.833',
    'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.4.9999.1900 Safari/537.31 BDSpark/26.4',
    
//     // feature_phone.yml
//     'Mozilla/5.0 (SymbianOS/9.2;U; Series60/5.0 SonyEricssonU5i/1.00;Profile/MIDP-2.1 Configuration/ CLDC-1.1)AppleWebKit/525 (KHTML, like Gecko) version/3.0Safari/525',
//     'Sagem-my411X/1.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 Browser/UP.Browser/7.2.6.c.1.326 (GUI)',
    
//     // feed_reader.yml
//     'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.21 (KHTML, like Gecko) akregator/4.11.5 Safari/537.21',
//     'RSSOwl/2.2.1.201312301314 (Windows; U; en)',
    
//     // mediaplayer.yml
//     'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.28) Gecko/20130316 Nightingale/1.12.2 (20140112193149)',
//     'iTunes/10.2.1 (Windows; Microsoft Windows 7 Enterprise Edition Service Pack 1 (Build 7601)) AppleWebKit/533.20.25',
    
//     // mobile_apps.yml
//     'AndroidDownloadManager/4.1.1 (Linux; U; Android 4.1.1; MB886 Build/9.8.0Q-97_MB886_FFW-20)',
//     'Mozilla/5.0 (iPad3,6; iPad; U; CPU OS 7_1 like Mac OS X; en_US) com.google.GooglePlus/33839 (KHTML, like Gecko) Mobile/P103AP (gzip)',
    
//     // phablet.yml
//     'Mozilla/5.0 (Linux; Android 4.1.2; ME371MG Build/JZO54K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.166 Safari/537.36',
//     'Mozilla/5.0 (Linux; U; Android 4.1.2; zh-cn; GN810 Build/JZO54K) AppleWebKit/534.24 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.24 T5/2.0 baiduboxapp/6.6.1 (Baidu; P1 4.1.2)',

//     // portable_media_player.yml
//     'Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_0 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Mobile/4B2086',
//     'Mozilla/5.0 (Linux; U; Android 2.3.6; ko-kr; YP-GB1 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
    
//     // smart_display.yml
//     'Mozilla/5.0 (Linux; U; Android 4.0.4; de-de; VSD220 Build/IMM76D.UI23ED12_VSC) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30',
//     'Mozilla/5.0 (Linux; U; Android 4.0.4; fr-be; DA220HQL Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30',
    
//     // tablet.yml
//     'Mozilla/5.0 (Android; Tablet; rv:26.0) Gecko/26.0 Firefox/26.0',
//     'Mozilla/5.0 (Linux; U; Android; en-us; LC0808B Build/FRF91) AppleWebKit/533.1',
//     'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; WOW64; Trident/6.0; Touch; MAARJS)',
    
//     // tv.yml
//     'Opera/9.80 (Linux sh4 ; U; HBBTV/1.0 (; LOH/1.00; -----;;;) CE-HTML/1.0 Config(L:de,CC:AT); en) Presto/2.5.21 Version/10.30',
//     'Mozilla/5.0 (Linux i686; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.114 Safari/537.36 SRAF/3.0 HbbTV/1.1.1 (CHANGHONG; TV55; sw-v1.0;) CE-HTML/1.0 NETRANGEMMH',
    
//     // unknown.yml
//     'Mozilla/3.01 (compatible;)',
//     'Mozilla/5.0 (Linux; U; Android 4.0.4; en-us; Gadmei ICS Build/E8HD-T364-1280x768) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30',
//     'Mozilla/5.0 (Linux; U; Android 2.3.6; zh-cn; SPHS on Hsdroid Build/MocorDroid2.3.5) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
];

/*
 * Providers
 */
$chain = new Provider\Chain([
    new Provider\DeviceDetector(),
    new Provider\UAParser(),
    new Provider\PhpBrowscap(),
    new Provider\PhpUserAgent(),
    new Provider\Woothee(),
//     new Provider\Sail(),
    new Provider\WhichBrowser()
    
]);
$chain->setExecuteAll(true);

$matrix = new GetMatrix();
$matrix->setUserAgents($userAgents);
$matrix->setProvider($chain);

ob_start();
?>
<html>
<head>
<link
	href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" />
	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</head>

<body>
        <?= $matrix->toHtml(); ?>
</body>
</html>
<?php
$output = ob_get_contents();
file_put_contents('data/example2.html', $output);
