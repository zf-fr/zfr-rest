ZfrRest
=======

[![Build Status](https://travis-ci.org/zf-fr/ZfrRest.png?branch=master)](https://travis-ci.org/zf-fr/ZfrRest)


ZfrRest is a Zend Framework 2 module that aims to simplify the development of RESTful applications. It contains various
components that can be used all together or separately.

> This module is currently in heavy development and its API may change in the future.


Dependencies
------------

ZfrRest depends on the following libraries:

* Zend Framework 2 (>= 2.1)
* Doctrine\Common (>= 2.4)
* Symfony\Serializer (>= 2.1)

This module also suggests some other modules that are optional but can be useful:

* Outeredge\SwaggerModule (latest): this module allows to easily generate Swagger documentation of your REST API.


Installation
------------

Installation of ZfrRest uses Composer. For Composer documentation, please refer to [getcomposer.org](http://www.getcomposer.org). Installation
without Composer is not officially supported.

1. Add the following line to your ``composer.json`` file: ``"zfr/zfr-rest": "dev-master"``
2. Install the module and all its dependencies by typing the following command: ``php composer.phar update``
3. Add the ``ZfrRest`` key to your ``config/application.config.php`` file.


Running the tests
-----------------

ZfrRest is fully tested. You can run the tests by typing ``phpunit`` command directly in ZfrRest folder.


Documentation
-------------

### English

The English documentation can be found [here](https://github.com/zf-fr/ZfrRest/blog/master/docs/english).

### French

La documentation en français sera disponible dès que l'API commencera à être gelée.
