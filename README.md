ZfrRest
=======

[![Build Status](https://travis-ci.org/zf-fr/zfr-rest.png?branch=master)](https://travis-ci.org/zf-fr/zfr-rest)
[![Coverage Status](https://coveralls.io/repos/zf-fr/zfr-rest/badge.png?branch=master)](https://coveralls.io/r/zf-fr/zfr-rest?branch=master)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/zf-fr/zfr-rest/badges/quality-score.png?s=78ed408c927e01cb27ab7f3cc04349a770132550)](https://scrutinizer-ci.com/g/zf-fr/zfr-rest/)
[![Latest Stable Version](https://poser.pugx.org/zfr/zfr-rest/v/stable.png)](https://packagist.org/packages/zfr/zfr-rest)
[![Total Downloads](https://poser.pugx.org/zfr/zfr-rest/downloads.png)](https://packagist.org/packages/zfr/zfr-rest)
[![Dependency Status](https://www.versioneye.com/package/php--zfr--zfr-rest/badge.png)](https://www.versioneye.com/package/php--zfr--zfr-rest)

## Installation

Install the module by typing (or add it to your `composer.json` file):

`php composer.phar require zfr/zfr-rest:0.4.*`

Then, add the keys "ZfrRest" to your modules list in `application.config.php` file, and copy-paste the file
`zfr_rest.global.php.dist` into your `autoload` folder (don't forget to remove the .dist extension at the end!).

## ZfrRest 0.4+ vs ZfrRest 0.3

Starting from version 0.4, ZfrRest has been completely rewritten from scratch. Previously, ZfrRest used to do a lot
of things automatically for you, from rendering, to automatic routing and creation of routes. However, while nice for
very simple use cases, it was actually very hard to extend, introduced a lot of performance problems and was quite
unflexible.

Now, ZfrRest is more a "small REST utility". It provides a simple way to handle HTTP exceptions, a lightweight
controller that can both handle action and HTTP verbs, and a view layer adapted for resource rendering (that can
optionally use versioning to render a resource differently based on the version). You can consider ZfrRest as a
module that you can use if you want to create a REST API, instead of a full-blown module like Apigility or previous
ZfrRest versions.

Additionally, dependency to Doctrine has been completely remove and can be used by anyone.

## ZfrRest vs Apigility

[Apigility](http://www.apigility.org) is a Zend Framework 2 API builder that also aims to simplify the creation of
REST APIs.

Starting from ZfrRest 0.4+, there is actually nothing in common between those two libraries. Apigility is a full-blown
module that does a lot of things, from headers negotiation, automatic rendering, link generation, a code-generator...
On the other hand, ZfrRest is the bare metal, and leave most of the responsability and power to you.

## Documentation

The official documentation is available in the [/docs](/docs) folder.
