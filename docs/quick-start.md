# Quick start

## Introduction

ZfrRest is a module that aims to simplify the creation of RESTful applications. It is tightly integrated to
Doctrine\Common interfaces. Therefore, people already using Doctrine (ORM, ODM…) can start to write ZfrRest with nearly no code.

## Initial setup

Once you have installed the module and copied the `zfr_rest.local.php` file into your `autoloader` folder, it's start
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
		'object_manager' => 'doctrine.documentmanager.orm_default'
	)
);
```

Users that are not using Doctrine can [learn more in the cook-book](/cook-book.md) about how to use with other
persistence layer like Zend\Db.

## Entity

We are going to create a very simple REST applications that interact with users. We want to be able to create, read,
delete and update users. Let's first create the entity:

```php
<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfrRest\Resource\Metadata\Annotation as REST;

/**
 * @ORM\Entity
 * @ORM\Table(name="Users")
 * @REST\Resource(controller="Application\Controller\UserController")
 * @REST\Collection(controller="Application\Controller\UserListController")
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @param int $id
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
```

This entity contains the minimal mapping we need for ZfrRest, but we are going to add more data as soon as we
progress. The ORM annotations are specific to Doctrine, so please read their documentation for more information.

Notice the @REST\Resource annotation. This mapping is specific to ZfrRest. This annotation can accept multiple
attributes, but here we only use the "controller" attribute. This is to say to ZfrRest to map the user resource to
a specific controller. The @REST\Collection is used in cases when the resource is a collection of objects. Indeed,
in a REST model, an user and a list of users are two distinct resources. That's why we provide different mappings.

Said otherwise, typing "/users" will be dispatched to the `UserListController` (it is a collection of users) while
typing "/users/4" will be dispatched to the `UserController`.

As any Zend Framework 2 controllers, we need to add them to the controllers plugin manager. In your `module.config.php`
file, add the following:

```php
'controllers' => array(
    'invokables' => array(
        'Application\Controller\UserController'     => 'Application\Controller\UserController',
        'Application\Controller\UserListController' => 'Application\Controller\UserListController',
    ),
),
```

## Configurate the mapping

We are using annotations, so in order to ZfrRest to "read" the mapping, we must add a driver. Drivers in ZfrRest work
pretty the same way as for Doctrine. In your `module.config.php`, add the following:

```php
return array(
	'zfr_rest' => array(
        'object_manager' => 'doctrine.entitymanager.orm_default',

        'resource_metadata' => array(
            'drivers' => array(
                'annotation_driver' => array(
                    'class' => 'ZfrRest\Resource\Metadata\Driver\AnnotationDriver'
                )
            )
        )
    ),
);
```

> In DoctrineORMModule for instance, you need to add one driver per module. In ZfrRest, the annotation driver is
"global" to the whole application. However, PHP mapping is a bit more verbose as you need to set paths. More
information can be found [in the PHP mapping reference](/php-mapping-reference.md).

## Writing the route

If we type "/users" in our browser, nothing happen (yet). This is because we need to create a route. The route is an
entry-point to a specific resource. What is nice, however, is that associated resources are automatically discovered.
For instance, if your User entity has an association with a collection of Tweet resources, you don't need to write two
routes! More on that later.

In your `module.config.php` file, add the following route:

```php
return array(
	'router' => array(
        'routes' => array(
            'users' => array(
                'type'    => 'ResourceGraphRoute',
                'options' => array(
                    'route'    => '/users',
                    'resource' => 'Application\Repository\UserRepository'
                )
            )
        )
    ),
);
```

The type of the route, `ResourceGraphRoute`, is a route provided by ZfrRest. Notice the `resource` option. The object
identified by the `resource` option is an object that is used to retrieve resources of a specific type. Everything in
ZfrRest is retrieved using the service locator for easier dependencies handling. Therefore, we need to add a factory
in the service manager to tell Zend Framework how to retrieve it:

```php
return array(
	'service_manager' => array(
        'factories' => array(
            'Application\Repository\UserRepository' => function($sl) {
                $objectManager = $sl->get('doctrine.entitymanager.orm_default');
                return $objectManager->getRepository('Application\Entity\User');
            }
        )
    ),
);
```

If writing a factory for each repository is too tiring, you can always write an abstract factory for that.

Now, let's type "/users" in our browser. Here is what happen:

![ZfrRest](/img/zfr-rest-1.png)

It throws an exception, which is normal. This is because we didn't write our controller.

## Controllers

Let's make this query work by writing the `get` method in the `UserListController`. This is because "/users" returns a
collection of users, so it belongs to the controller defined in the Collection mapping. Please notice that all
controllers must extend from `ZfrRest\Mvc\Controller\AbstractRestfulController`:

```php
<?php

namespace Application\Controller;

use Zend\Paginator\Paginator;
use ZfrRest\Mvc\Controller\AbstractRestfulController;

class UserListController extends AbstractRestfulController
{
    /**
     * @param  Paginator $users
     * @return ViewModel
     */
    public function get(Paginator $users)
    {
        $users->setCurrentPageNumber(1);

        return $users;
    }
}
```

As you can see, ZfrRest automatically created a paginator for us. This is because it knows it is a collection. Notice
also that we directly return the paginator. In fact, ZfrRest is smart enough to choose the appropriate view model type
based on the Accept header. For instance, if the Accept header is `application/json`, a JsonModel will be created for
you. Here is the output if the database contains one user:

```json
{
    "current_page": 1,
    "count_per_page": 10,
    "items": [
    {
        "id": 1,
        "name": "Michael"
    }
]}
```

You can also "force" ZfrRest to always return a specific type by returning a ViewModel instance.

Now, let's write the POST method. It belongs to the `UserListController` because we are adding a new resource to a
collection! Hopefully, it's damn simple:

```php
<?php

namespace Application\Controller;

use Application\Entity\User;
use ZfrRest\Mvc\Controller\AbstractRestfulController;

class UserListController extends AbstractRestfulController
{
    public function post(User $user)
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $em->persist($user);
        $em->flush();
    }
}
```

ZfrRest did a lot of us here. It extracted the data from the body, validated it, hydrated it and gave us the result.
The only thing we need to do is our true logic (persist the object, send an email…).

However, you may ask *how* ZfrRest can validate data. In fact, for this to work, you need to add an attribute to the
Resource mapping, as shown below:

```php
/**
 * @ORM\Entity
 * @ORM\Table(name="Users")
 * @REST\Resource(
 *    controller="Application\Controller\UserController",
 *    inputFilter="Application\InputFilter\UserInputFilter"
 * )
 */
class User
{
```

The UserInputFilter is fetched from the input filter plugin manager. So if you have complex dependencies for an input
filter, you can create a factory:

```php
// in module.config.php
return array(
	'input_filters' => array(
		'factories' => array(
			'MyComplexInputFilter' => 'Application\InputFilter\MyComplexInputFilterFactory'
		)
	)
);
```

If the input filter does not validate, ZfrRest will automatically returns a 400 error, with an "errors" key that
contains the fields that failed.

Note that for POST requests, ZfrRest automatically returns a 201 answer (Created), and add a Location header with the
link to the newly created resource.

> Note: if you don't want ZfrRest to auto-validate and auto-hydrate values for you, you can! Instead of objects, you
will receive plain array, and it's up to you to create the input filter and hydrate the data. See the cook-book for
learning how to do it.

The GET for a single item, DELETE and PUT are then written in the `UserController`:

```php
<?php

namespace Application\Controller;

use Application\Entity\User;
use Zend\View\Model\ViewModel;
use ZfrRest\Mvc\Controller\AbstractRestfulController;

class UserController extends AbstractRestfulController
{
    public function get(User $user)
    {
        return $user;
    }

    public function put($user)
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $em->flush();
        return $user;
    }

    public function delete(User $user)
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $em->remove($user);
        $em->flush();
    }
}
```

We can't make it simpler! Once again, ZfrRest does everything for you!

### Throw exceptions

In your controller actions, you may want to return specific HTTP status code (this is a good practice for nice APIs!).
ZfrRest make it really simple. For instance, if you want to disallow access to a specific resource:

```php
use ZfrRest\Http\Exception\Client;

public function get(User $user)
{
    if ($this->request->fromQuery('token') !== 'valid') {
    	throw new Client\UnauthorizedException();
    }

    return $user;
}
```

ZfrRest has built-in exceptions for the most common ones.


## Associations

