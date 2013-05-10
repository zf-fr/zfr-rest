# Annotations reference

In this chapter a reference of ZfrRest annotations is given.

## Index

## Reference

### Association

This annotation is used to mark an association. This annotation can only be used at property level.

*Optional attributes:*

* **allowTraversal**: (default to false) If this attribute is set to true, then the association is exposed by the router, hence allowing dispatching to the associated resource.
* **serializationStrategy**: (default to "IDENTIFIERS") Define how the association is serialized. This value can be "IDENTIFIERS" (only identifiers is outputed), "LOAD" (the resources are loaded and sent with the resource) or "NONE" (the association is ignored).

*Example:*

```php
/**
 * @var Collection
 * @REST\Association(allowTraversal=true, serializationStrategy="NONE")
 */
protected $tweets;
```

### Collection

This annotation is used to define mapping about a collection of a given resource. This annotation basically define the same information than Controller, Hydrator and InputFilter annotations, but in a collection context. This annotation can only be used at class level.

*Required attributes:*

* **controller**: FQCN of the controller to use.

*Optional attributes:*

* **inputFilter**: FQCN of the input filter to use. If not set, it will reuse the input filter set at the class level.
* **hydrator** : FQCN of the hydrator to use. If not set, it will reuse the hydrator set at the class level.
* **paginate**: (default to true) If this attribute is set to true, then the elements are wrapped around a Zend\Paginator instance.

*Example:*

```php
/**
 * @REST\Collection(
 *    controller="Application\Controller\UserListController",
 *    paginate=true
 * )
class User
{
   // ...
}
```

### Controller

This annotation is used to define the controller the resource is dispatched to if it is matched. The controller must be added
to the controllers plugin manager, like any other Zend Framework 2 controllers. It must be a subclass of `ZfrRest\Mvc\Controller\AbstractRestfulController`. This annotation can only be used at class level.

*Required attribute:*

* **name**: FQCN of the controller to use.

*Example:*

```php
/**
 * @REST\Controller(name="Application\Controller\UserController")
 */
class User
{
   // ...
}
```

### Hydrator

This annotation is used to define the hydrator. The hydrator must be added to the hydrator plugin manager. This annotation can only be used at class level.

*Required attribute:*

* **name**: FQCN of the hydrator to use.

*Example:*

```php
/**
 * @REST\Hydrator(name="Application\Hydrator\UserHydrator")
 */
class User
{
   // ...
}
```

### InputFilter

This annotation is used to define the input filter for the resource. The input filter must be added to the input filter plugin manager. This input filter is used to validate data for POST and PUT verbs. This annotation can only be used at class level.

*Required attribute:*

* **name**: FQCN of the input filter to use.

*Example:*

```php
/**
 * @REST\InputFilter(name="Application\InputFilter\UserInputFilter")
 */
class User
{
   // ...
}
```
