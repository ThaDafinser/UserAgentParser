# UserAgentParser
[![Build Status](https://travis-ci.org/ThaDafinser/UserAgentParser.svg)](https://travis-ci.org/ThaDafinser/UserAgentParser)
[![Code Coverage](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/UserAgentParser/?branch=master)

Different UA parse provider

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

## How to build
`composer install -o`

`php vendor\browscap\browscap\bin\browscap build 6009`

`php bin\initCache.php`

`php bin\generateMatrixAll.php`
