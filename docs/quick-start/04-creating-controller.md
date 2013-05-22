# Quick start

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
    "items": [{
        "id": 1,
        "name": "Michael"
    }]
}
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

ZfrRest has built-in exceptions for the most common `4xx` and `5xx` HTTP response codes.

[In next part](/05-associations.md), you are going to learn how to use associations with ZfrRest.
