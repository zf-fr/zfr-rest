# Quick start

## Introduction

ZfrRest is a module that aims to simplify the creation of RESTful applications. It is tightly integrated to
Doctrine\Common interfaces. Therefore, people already using Doctrine (ORM, ODM…) can start to write ZfrRest with nearly no code.

## Initial setup

Once you have installed the module and copied the `zfr_rest.global.php` file into your `autoloader` folder, it's start
to configure it. This file contains a lot of options (nearly everything can be configured in ZfrRest!), but we are
going to update the `object_manager` key. As I said earlier, ZfrRest is based on `Doctrine\Common` interfaces, where
the object manager is an object that is used as a persistence layer.

If you are using DoctrineORMModule, the setup is pretty easy:

```php
return array(
    'zfr_rest' => array(
        'object_manager' => 'doctrine.entitymanager.orm_default'
    )
);
```

Users of DoctrineMongoODMModule:

```php
return array(
	'zfr_rest' => array(
		'object_manager' => 'doctrine.documentmanager.odm_default'
	)
);
```

Users that are not using Doctrine can [learn more in the cook-book](../cook-book.md) about how to use with other
persistence layer like Zend\Db.

[In next part](02-define-entity.md), you are going to define your entity.
