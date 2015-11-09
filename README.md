# UserAgentParser
[![Build Status](https://travis-ci.org/ThaDafinser/UserAgentParser.svg)](https://travis-ci.org/ThaDafinser/UserAgentParser)
[![Code Coverage](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/?branch=master)

| Provider | Browser | RenderingEngine | Operating system | Device | Bot | Only PHP | Comment |
| --- | --- | --- | --- | --- | --- | --- |
| [BrowscapPhp](https://github.com/browscap/browscap-php) | yes | yes | yes | yes | yes | no | lite and full version available |
| [DonatjUAParser](https://github.com/donatj/PhpUserAgent) | yes | jiein | no | jiein | no | yes | |
| [PiwikDeviceDetector](https://github.com/piwik/device-detector) | yes | yes | yes | yes | yes | yes | |
| [UAParser](https://github.com/ua-parser/uap-php) | yes | no | yes | yes | yes | no | |
| [WhichBrowser](https://github.com/WhichBrowser/WhichBrowser) | yes | yes | yes | yes | yes | no | |
| [Woothee](https://github.com/woothee/woothee-php) | yes | no | jiein | jiein | yes | no | |
| [YzalisUAParser](https://github.com/yzalis/UAParser) | yes | yes | yes | yes | no | yes | |
Different UA parse provider

## Installation
```
composer require thadafinser/user-agent-parser
```

## Example
```php
require 'vendor/autoload.php';

use UserAgentParser\Provider;

$userAgent = 'Mozilla/5 (X11; Linux x86_64) AppleWebKit/537.4 (KHTML like Gecko) Arch Linux Firefox/23.0 Xfce';

$dd = new Provider\YzalisUAParser();

/* @var $result \UserAgentParser\Model\UserAgent */
$result = $dd->parse($userAgent);
var_dump($result->toArray());
```

## Providers

### Comparison matrix
Here is a comparison matrix, with many analyzed UserAgent strings, to help you device which provider fits your needs:
[Go to the matrix](https://github.com/ThaDafinser/UserAgentParserMatrix)

### Overview

| Provider | Browser | RenderingEngine | Operating system | Device | Bot | Comment |
| --- | --- | --- | --- | --- | --- | --- |
| [BrowscapPhp](https://github.com/browscap/browscap-php) | yes | yes | yes | yes | yes | lite and full version available |
| [DonatjUAParser](https://github.com/donatj/PhpUserAgent) | yes | jiein | no | jiein | no | |
| [PiwikDeviceDetector](https://github.com/piwik/device-detector) | yes | yes | yes | yes | yes | |
| [UAParser](https://github.com/ua-parser/uap-php) | yes | no | yes | yes | yes | |
| [WhichBrowser](https://github.com/WhichBrowser/WhichBrowser) | yes | yes | yes | yes | yes | |
| [Woothee](https://github.com/woothee/woothee-php) | yes | no | jiein | jiein | yes | |
| [YzalisUAParser](https://github.com/yzalis/UAParser) | yes | yes | yes | yes | no | |

### BrowscapPhp
To run this provider you need to generate the cache first.
You can also choose between the `lite` and `full` version. Of course the lite version is faster, but does not contain all informations

#### Lite version
```php
require 'vendor/autoload.php';

use BrowscapPHP\Browscap;
use BrowscapPHP\Cache\BrowscapCache;
use BrowscapPHP\Command;
use BrowscapPHP\Exception;
use WurflCache\Adapter\File;

$cacheAdapter = new File(array(
    File::DIR => '.tmp/browscap_lite'
    // File::DIR => '.tmp/browscap_full'
));
$cache = new BrowscapCache($cacheAdapter);

$parser = new Browscap();
$parser->setCache($cache);
$parser->convertFile('vendor/browscap/browscap/build/php_browscap.ini');
//$parser->convertFile('vendor/browscap/browscap/build/full_php_browscap.ini');
```

#### Full version
```php
require 'vendor/autoload.php';

use BrowscapPHP\Browscap;
use BrowscapPHP\Cache\BrowscapCache;
use BrowscapPHP\Command;
use BrowscapPHP\Exception;
use WurflCache\Adapter\File;

$cacheAdapter = new File(array(
    File::DIR => '.tmp/browscap_full'
));
$cache = new BrowscapCache($cacheAdapter);

$parser = new Browscap();
$parser->setCache($cache);
$parser->convertFile('vendor/browscap/browscap/build/full_php_browscap.ini');
```

## How to build
`composer install -o`

`php vendor\browscap\browscap\bin\browscap build 6009`

`php bin\initCache.php`

`php bin\generateMatrixAll.php`
