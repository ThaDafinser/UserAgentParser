# UserAgentParser
[![Build Status](https://travis-ci.org/ThaDafinser/UserAgentParser.svg)](https://travis-ci.org/ThaDafinser/UserAgentParser)

Different UA parse provider + comparison

# How to build
`composer install -o`

`php vendor\browscap\browscap\bin\browscap build 6009`

`php bin\initCache.php`

`php bin\generateMatrixAll.php`

# get the comparison results
Download the html files from `data/results` and just open it in a browser

## Notes about the comparison
The different vendor adapters are not completed yet and may have failures or missing things, so the result is not 100% correct!
