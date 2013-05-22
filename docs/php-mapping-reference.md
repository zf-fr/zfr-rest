# PHP mapping reference

In this chapter a reference of ZfrRest PHP mapping is given. Before using PHP mapping, you must carefully add the
paths for ALL your mapping. For instance, let's say we want to define mapping for all entities that belong to the
`Application\Entity` namespace. We first need to add the following lines to your `module.config.php` file:

```php
'zfr_rest' => array(
    'resource_metadata' => array(
        'drivers' => array(
            'application_driver' => array(
                'class' => 'ZfrRest\Resource\Metadata\Driver\PhpDriver',
                'paths' => array(
                    'Application\Entity' => __DIR__ . '/../config/mapping'
                )
            )
        )
    ),
)
```

Next, you need ot add one PHP file by entity, whose name is the name of the entity itself. For instance, if you have
an entity called "User", you need to create a `User.php` file into the `config/mapping` folder we mapped above.

To keep your code clean, we recommand you to add this snippet to each of your `module.config.php` file.

## Index

## Reference

### Associations

This mapping is used to mark an association between two resources. This mapping can only be used as a top key. It is
a multi-dimensional array whose key is the name of the association.

*Optional attributes:*

* **allow_traversal**: (default to false) If this attribute is set to true, then the association is exposed by the router,
hence allowing dispatching to the associated resource.
* **serialization_strategy**: (default to IDENTIFIERS) Define how the association is serialized when the parent resource
is outputted. Can be "IDENTIFIERS" (only output ids) or "LOAD" (load the associated records). For avoiding any BC, please
use the ResourceMetadataInterface constants instead, as shown below:

*Example:*

```php
return array(
	'associations' => array(
		'tweets' => array(
			'allow_traversal'        => false,
			'serialization_strategy' => ResourceMetadataInterface::SERIALIZATION_STRATEGY_IDENTIFIERS
		)
	)
);
```

### Collection

This mapping is used to define mapping about a collection of a given resource. This mapping basically define
the same information than resource mapping, but in a collection context. This mapping can be used both as a top key
or inside an association key.

*Required attributes:*

* **controller**: FQCN of the controller to use.

*Optional attributes:*

* **input_filter**: FQCN of the input filter to use. If not set, it will reuse the input filter set in the `Resource` annotation.
* **hydrator** : FQCN of the hydrator to use. If not set, it will reuse the hydrator set in the `Resource` annotation.

*Example:*

```php
return array(
	'collection' => array(
		'controller'   => 'Application\Controller\UserListController',
		'input_filter' => 'Application\InputFilter\UserInputFilter'
	),

	'associations' => array(
		'tweets' => array(
			// Inside an association to override generic mapping
			'collection' => array(
				'controller' => 'Application\Controller\SuperTweetController'
			)
		)
	)
);
```

### Resource

This mapping is used to define the resource's mapping. This mapping can be used both as a top key
or inside an association key.

*Optional attributes:*

* **controller**: FQCN of the controller to use. The controller must be added to the controllers plugin manager,
like any other Zend Framework 2 controllers. It must be a subclass of `ZfrRest\Mvc\Controller\AbstractRestfulController`.
* **inputFilter**: FQCN of the input filter to use. The input filter must be added to the input
filter plugin manager. This input filter is used to validate data for POST and PUT verbs. Note that this
attribute is **required** if you activate the *auto_validate* option (which is true by default).
* **hydrator**: FQCN of the hydrator to use. The hydrator must be added to the hydrator plugin manager.  By default,
it uses the DoctrineModule hydrator (`DoctrineModule\Stdlib\Hydrator\DoctrineObject`)

*Example:*

```php
return array(
	'resource' => array(
		'controller'   => 'Application\Controller\UserController',
		'input_filter' => 'Application\InputFilter\UserInputFilter'
	),

	'associations' => array(
		'tweets' => array(
			// Inside an association to override generic mapping
			'resource' => array(
				'controller' => 'Application\Controller\SuperTweetController'
			)
		)
	)
);
```

## Complete example

Here is a complete example (this mapping is for a User class, so it's inside a `User.php` file):

```php
return array(
	'resource' => array(
		'controller'   => 'Application\Controller\UserController',
		'input_filter' => 'Application\InputFilter\UserInputFilter'
	),

	// Here it will reuse the input filter defined in "resource" key. The collection
	// mapping is used when we reach a URL that represent a collection (for instance /users)
	'collection' => array(
		'controller'   => 'Application\Controller\UserListController'
	),

	'associations' => array(
		// This will allow the following route: /users/:id/tweets

		'tweets' => array(
			// Inside an association to override generic mapping. We also override the controller used
			'resource' => array(
				'controller' => 'Application\Controller\SuperTweetController'
			)
		)
	)
);
```
