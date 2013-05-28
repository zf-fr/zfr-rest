ZfrRest
=======

[![Build Status](https://travis-ci.org/zf-fr/zfr-rest.png?branch=master)](https://travis-ci.org/zf-fr/zfr-rest) [![Coverage Status](https://coveralls.io/repos/zf-fr/zfr-rest/badge.png?branch=master)](https://coveralls.io/r/zf-fr/zfr-rest?branch=master) [![Dependency Status](https://www.versioneye.com/package/php--zfr--zfr-rest/badge.png)](https://www.versioneye.com/package/php--zfr--zfr-rest)

Version 0.1.0 ([changelog](/CHANGELOG.md))

**[READ MORE ABOUT CURRENT STATUS](https://github.com/zf-fr/ZfrRest/issues/41)**

## Is ZfrRest usable ?

Current features of ZfrRest work well and ZfrRest is definitely usable for simple cases. However, please note that
we have many more features to come for more complex applications. Those changes may (well, for sure they will) break
the API at some point, but we will keep track of all the changes in the [UPGRADE](UPGRADE.md) guide.

Ultimately, you are strongly encouraged to test the module and report feedbacks, PR...

## Milestone

Here is a basic roadmap of ZfrRest, by priority:

1. Finish the work on the ResourceGraphRoute, especially the assemble method.
2. Better architecture for extracting/hydrating data, to especially allow to configure key names, payload structure...
(so that integrate ZfrRest with a MVC framework that has specific conventions to be as easy as write an adapter).
3. Add support for HATEOAS links.
4. Improve the performance (using proxy maybe useful in some places)
5. Cleanup the code

## Installation

Install the module by typing (or add it to your `composer.json` file):

`php composer.phar require zf-fr/zfr-rest`

Then, add the keys "ZfrRest" to your modules list in `application.config.php` file, and copy-paste the file
`zfr_rest.local.php.dist` into your `autoload` folder (don't forget to remove the .dist extension at the end!). For
more details about how to use ZfrRest, please follow the [quick start]((/docs/quick-start.md)).

## Documentation

* [Quick Start](/docs/quick-start/01-introduction.md)
* [Annotation mapping reference](/docs/annotation-mapping-reference.md)
* [PHP mapping reference](/docs/php-mapping-reference.md)
* [Cook book](/docs/cook-book.md)
