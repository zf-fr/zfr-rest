ZfrRest
=======

[![Build Status](https://travis-ci.org/zf-fr/zfr-rest.png?branch=master)](https://travis-ci.org/zf-fr/zfr-rest)
[![Coverage Status](https://coveralls.io/repos/zf-fr/zfr-rest/badge.png?branch=master)](https://coveralls.io/r/zf-fr/zfr-rest?branch=master)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/zf-fr/zfr-rest/badges/quality-score.png?s=78ed408c927e01cb27ab7f3cc04349a770132550)](https://scrutinizer-ci.com/g/zf-fr/zfr-rest/)
[![Dependency Status](https://www.versioneye.com/package/php--zfr--zfr-rest/badge.png)](https://www.versioneye.com/package/php--zfr--zfr-rest)

## Installation

Install the module by typing (or add it to your `composer.json` file):

`php composer.phar require zfr/zfr-rest`

Then, add the keys "ZfrRest" to your modules list in `application.config.php` file, and copy-paste the file
`zfr_rest.global.php.dist` into your `autoload` folder (don't forget to remove the .dist extension at the end!). For
more details about how to use ZfrRest, please follow the [quick start](/docs/quick-start/01-introduction.md).

## Current limitations

ZfrRest currently suffers from the following flaws:

* ZfrRest only support POST and PUT for single resource (you cannot bulk create or bulk update)
* ZfrRest only supports JSON output
* Cannot assemble URLs from the router
* Links are not yet supported (ZfrRest is currently for target for internal used APIs rather than exposed APIs)
* ManyToMany associations are not yet supported (ie. URI like http://example.com/countries/capitals)
