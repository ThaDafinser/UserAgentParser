
# Local providers

A detailed description of all local providers

- [BrowscapPhp](#browscapphp)
- [DonatjUAParser](#donatjuaparser)
- [PiwikDeviceDetector](#piwikdevicedetector)
- [SinergiBrowserDetector](#sinergibrowserdetector)
- [UAParser](#uaparser)
- [WhichBrowser](#whichbrowser)
- [Woothee](#woothee)

## BrowscapPhp

Is a provider which exists now for a really long time.
The data is also used in the PHP core function `get_browser()` (which is really slow)

To get this provider working, you need todo some work before.
There are `multiple ways` to install this provider and update / cache it.

If you want another method then described here, please see the official documentation [here](https://github.com/browscap/browscap-php)
- other cache types available
- directly download and update the data file
- ...

### Install

```
composer require browscap/browscap-php 
```

### Download `browscap.ini`

Go to http://browscap.org/ and get the latest download for PHP
You can take `full` or `lite`. Both should work.


### Warmup the cache

We assume that you have put the downloaded file to `data/full_php_browscap.ini`

```php
use BrowscapPHP\Browscap;
use BrowscapPHP\Helper\IniLoader;
use WurflCache\Adapter\File;

/*
 * Browscap cache init
 */
include 'bootstrap.php';

/*
 * File
 */
$cache = new File([
    File::DIR => '.tmp/browscap'
]);

$bc = new Browscap();
$bc->setCache($cache);
$bc->convertFile('data/full_php_browscap.ini');
```


### Use it

We use directly the already created cache and should have fast detection results.

```php
use BrowscapPHP\Browscap;
use UserAgentParser\Provider;
use WurflCache\Adapter\File;

$cache = new File([
    File::DIR => '.tmp/browscap'
]);

$browscapParser = new Browscap();
$browscapParser->setCache($cache);

$provider = new Provider\BrowscapPhp($browscapParser);

$result = $provider->parse($userAgent, $headers);
```


## DonatjUAParser


### Install

```
composer require donatj/phpuseragentparser
```


### Use it

```php
use UserAgentParser\Provider;

$provider = new Provider\DonatjUAParser();

$result = $provider->parse($userAgent, $headers);
```


## PiwikDeviceDetector


### Install

```
composer require piwik/device-detector
```

### Use it (without cache)

```php
use UserAgentParser\Provider;

$provider = new Provider\PiwikDeviceDetector();

$result = $provider->parse($userAgent, $headers);
```

### Use it (with cache)

```php
use Doctrine\Common\Cache;
use UserAgentParser\Provider;

$piwikParser = new \DeviceDetector\DeviceDetector();
$piwikParser->setCache($new Cache\PhpFileCache('.tmp/piwik'));

$provider = new Provider\PiwikDeviceDetector($piwikParser);

$result = $provider->parse($userAgent, $headers);
```


## SinergiBrowserDetector


### Install

```
composer require sinergi/browser-detector
```


### Use it

```php
use UserAgentParser\Provider;

$provider = new Provider\SinergiBrowserDetector();

$result = $provider->parse($userAgent, $headers);
```


## UAParser


### Install

```
composer require ua-parser/uap-php
```


### Use it

```php
use UserAgentParser\Provider;

$provider = new Provider\UAParser();

$result = $provider->parse($userAgent, $headers);
```


## WhichBrowser


### Install

```
composer require whichbrowser/parser
```


### Use it

```php
use UserAgentParser\Provider;

$provider = new Provider\WhichBrowser();

$result = $provider->parse($userAgent, $headers);
```


## Woothee


### Install

```
composer require woothee/woothee
```


### Use it

```php
use UserAgentParser\Provider;

$provider = new Provider\Woothee();

$result = $provider->parse($userAgent, $headers);
```
