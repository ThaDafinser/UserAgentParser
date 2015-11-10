# UserAgentParser
[![Build Status](https://travis-ci.org/ThaDafinser/UserAgentParser.svg)](https://travis-ci.org/ThaDafinser/UserAgentParser)
[![Code Coverage](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/?branch=master)

`User agent` parsing is, was and will always be a painful thing, since it will never work 100% (since User agents are faked very often)!

The target of this package is to make it at least a bit less painful, by providing an abstract layer over many UserAgent parser around

So you can
- try out or switch between different parsers fast, without changing your code
- use multiple providers at the same time with the `Chain` provider
- compare the result of the different parsers [UserAgentParserMatrix](https://github.com/ThaDafinser/UserAgentParserMatrix)
- get always the same result model, regardless of which parser you use currently

## Installation
```
composer require thadafinser/user-agent-parser
```

## Example

### Single provider

```php
require 'vendor/autoload.php';

use UserAgentParser\Provider;

$userAgent = 'Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_5 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5';

$dd = new Provider\PiwikDeviceDetector();

/* @var $result \UserAgentParser\Model\UserAgent */
$result = $dd->parse($userAgent);

$result->getBrowser()->getName(); // Mobile Safari

$result->getOperatingSystem()->getName(); // iOS

$result->getDevice()->getBrand(); // iPod Touch
$result->getDevice()->getBrand(); // Apple
$result->getDevice()->getType(); // portable media player

$resultArray = $result->toArray();
```

### Chain provider
```php
require 'vendor/autoload.php';

use UserAgentParser\Provider;

$userAgent = 'Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_5 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5';

$browscapFull = new Provider\BrowscapPhp();
$browscapFull->setCachePath('.tmp/browscap_full');

$chain = new Provider\Chain([
    $browscapFull,
    new Provider\DonatjUAParser(),
    new Provider\PiwikDeviceDetector(),
    new Provider\UAParser(),
    new Provider\WhichBrowser(),
    new Provider\Woothee(),
    new Provider\YzalisUAParser()
]);

/* @var $result \UserAgentParser\Model\UserAgent */
$result = $dd->parse($userAgent);

$browserName = $result->getBrowser()->getName();
$deviceBrand = $result->getDevice()->getBrand();

var_dump($result->toArray());
```

## Providers

### Comparison matrix
Here is a comparison matrix, with many analyzed UserAgent strings, to help you device which provider fits your needs:
[Go to the matrix](https://github.com/ThaDafinser/UserAgentParserMatrix)

### Overview

| Provider | Browser | RenderingEngine | Operating system | Device | Bot | Only PHP | Comment |
| --- | --- | --- | --- | --- | --- | --- | --- |
| [BrowscapPhp](https://github.com/browscap/browscap-php) | yes | yes | yes | yes | yes | no | lite and full version available |
| [DonatjUAParser](https://github.com/donatj/PhpUserAgent) | yes | jiein | no | jiein | no | yes | |
| [PiwikDeviceDetector](https://github.com/piwik/device-detector) | yes | yes | yes | yes | yes | yes | |
| [UAParser](https://github.com/ua-parser/uap-php) | yes | no | yes | yes | yes | no | |
| [WhichBrowser](https://github.com/WhichBrowser/WhichBrowser) | yes | yes | yes | yes | yes | no | |
| [Woothee](https://github.com/woothee/woothee-php) | yes | no | jiein | jiein | yes | no | |
| [YzalisUAParser](https://github.com/yzalis/UAParser) | yes | yes | yes | yes | no | yes | |

### BrowscapPhp
To run this provider you need to generate the cache first.
You can also choose between the `lite` and `full` version. Of course the lite version is faster, but does not contain all informations

#### Generate the .ini files
You should grad the latest release of [browscap](https://github.com/browscap/browscap/releases) first and then change the version number at the end `6009` accordingly
```
php vendor\browscap\browscap\bin\browscap build 6009
```

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
