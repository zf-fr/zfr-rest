# Annotations reference

In this chapter a reference of ZfrRest annotations is given. For the examples to work, you must import a namespace:

```php
use ZfrRest\Resource\Metadata\Annotation as REST;
```

## Index

## Reference

### Association

This annotation is used to mark an association between two resources. This annotation can only be used at property level.

*Optional attributes:*

* **allowTraversal**: (default to false) If this attribute is set to true, then the association is exposed by the router,
hence allowing dispatching to the associated resource.

*Example:*

```php
/**
 * @var Collection
 * @REST\Association(allowTraversal=true, serializationStrategy="NONE")
 */
protected $tweets;
```

### Collection

This annotation is used to define mapping about a collection of a given resource. This annotation basically defines
the same information as a Resource annotation, but in a collection context. This annotation can be used at class level
and property level.

*Required attributes:*

* **controller**: FQCN of the controller to use.

*Optional attributes:*

* **inputFilter**: FQCN of the input filter to use. If not set, it will reuse the input filter set in the `Resource` annotation.
* **hydrator** : FQCN of the hydrator to use. If not set, it will reuse the hydrator set in the `Resource` annotation.

*Example:*

```php
/**
 * @REST\Collection(
 *    controller="Application\Controller\UserListController"
 * )
 */
class User
{
   // ...
}
```

### Resource

This annotation is used to define the resource's mapping. This annotation can be used at class level and property
level.

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
/**
 * @REST\Resource(
 *    controller="Application\Controller\UserController",
 *    inputFilter="Application\InputFilter\UserInputFilter",
 *    hydrator="DoctrineModule\Stdlib\Hydrator\DoctrineObject"
 * )
 */
class User
{
   // ...
}
```

## Complete example

Here is a complete example:

```php
/**
 * This example demonstrates a mapping for a User class.
 *
 * @REST\Resource(
 *    controller="Application\Controller\UserController",
 *    inputFilter="Application\InputFilter\UserInputFilter",
 *	  hydrator="DoctrineModule\Stdlib\Hydrator\DoctrineObject"
 * )
 *
 * The mapping defined in Collection is used when we reach a URL that
 * represent a collection (for instance /users)
 *
 * @REST\Collection(
 *    controller="Application\Controller\UserListController"
 * )
 */
class User
{
   /**
    * @var int
    */
   protected $id;

   /**
    * @var string
    */
   protected $firstName;

   /**
    * @var Collection
    *
    * This will allow the following route: /users/:id/tweets. We also override the controller used. By default, it
    * will reuse the mapping defined in the associated class (in this case, Tweet entity), but you may want to override
    * some attributes based on the context
    *
    * @REST\Assocation(allowTraversal=true, serializationStrategy="NONE")
    * @REST\Resource(
    *     controller="Application\Controller\UserTweetController")
    * )
    */
   protected $tweets;
}
```
