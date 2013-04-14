ZfrRest
=======

This documentation is up-to-date with current master branch (as of 13rd april 2013).

> Please note that this module is not ready for use. The API will change until we are all happy with it. I didn't
write tests yet, but I will.

## How you can help ?

I really need help with this module, as I lack some architecture skills. Here are some things that you can do:

* Review the existing codebase, especially the ZfrRest\Mvc namespace
* Find a nice architecture to allow HATEOAS links and output. For instance, when you return Paginator instances
from controller, I'd expect to run some kind of "view helpers" to output items, current page... I'm not sure yet
about where and when should this happen, and how to make it flexible.
* Find the best way to handle output of resources. What we'd like to support is a way to version API too. Currently,
the metadata contains hydrators, encoders and decoders, but I'm not satisfied with this approach.
* Add output strategy (for instance, EmberJS MVC framework expects output/input to be formatted a specific way, and
we should be able to swap libraries with minimal amount of work, typically by changing the NamingStrategy).
* Tests, tests, tests once we are happy with the code base !

EDIT: may be interesting to see if we cannot do anything with this concept: http://nicksda.apotomo.de/2011/12/ruby-on-rest-introducing-the-representer-pattern/

## Quick Start

This example is going to be super easy: we have a User and Tweet entities. We'd like to be able to access the
following routes: /users, /users/:id, /users/:id/tweets

### Mapping

Like Doctrine, ZfrRest defines mapping. This is used internally by ZfrRest, and currently you have two ways to
define mapping: either through annotations or PHP files. In this simple example, let's use annotations.

User.php:
```php
namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ZfrRest\Resource\Annotation as REST;

/**
 * @ORM\Entity
 * @REST\Controller(name="Application\Controller\User")
 * @REST\Collection(paginate=true, controller="Application\Controller\UserList")
 * @REST\Hydrator(name="Application\Hydrator\UserHydrator")
 * @REST\InputFilter(name="Application\InputFilter\UserInputFilter")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Tweet", mappedBy="user")
     * @REST\ExposeAssociation
     */
    protected $tweets;

    public function __construct()
    {
        $this->tweets = new ArrayCollection();
    }
}
```

Tweet.php:
```php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfrRest\Resource\Annotation as REST;

/**
 * @ORM\Entity
 * @REST\Controller(name="Application\Controller\Tweet")
 * @REST\Collection(paginate=true, controller="Application\Controller\TweetList")
 * @REST\Hydrator(name="Application\Hydrator\TweetHydrator")
 * @REST\InputFilter(name="Application\InputFilter\TweetInputFilter")
 */
class Tweet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="user")
     */
    protected $user;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
```

Mapping is pretty easy:

* @REST\Controller: defines which controller is used when a single resource is found (a User object).
* @REST\Collection: a User and a collection of users are two different resources. This is why we have two different
mappings for that. This mapping has two attributes: "paginate" which defines if the collection is wrapped around
a Zend\Paginator\Paginator (this is the case by default), and controller name to be dispatched when we have a
collection of this resource.
* @REST\Hydrator: by default, ZfrRest can do a lot of work for you automatically. When you receive data (from POST
or PUT requests for instance), it can automatically use your mapping to validate, filter and hydrate your data.
* @REST\InputFilter: input filter used by ZfrRest.

In $tweets property, we have the @REST\ExposeAssociation on the association tweets. This means that we tell to
ZfrRest that starting from a "User" object, we can access to the tweets resource. In other words, this "open" the
route "/users/:id/tweets". If this annotation was not here, this URL would have been a 404 error.

Note that $tweets association in User class will reuse the mapping define in the Tweet class, BUT you can override
some part. For instance, the Tweet mapping defines the collection controller as `Application\Controller\TweetList`.
This means that "/users/:id/tweets" will be dispatched to this controller. But you can override it:

```php
/**
 * @ORM\OneToMany(targetEntity="Tweet", mappedBy="user")
 * @REST\ExposeAssociation
 * @REST\Collection(paginate=true, controller="Application\Controller\AnotherController")
 */
protected $tweets;
```

Now, "/users/:id/tweets" will be dispatched to this new controller, BUT "/tweets" will still be dispatched to the
mapping defined in Tweet class.

> Everything is fetched from service locator, from hydrator to input filters. This way, you can define dependencies
easily.


### Specify driver

We need to indicate ZfrRest how to find these mappings. Similarily to what is done in Doctrine, we need to add
drivers.

Add the following code in your `module.config.php` :

```php
'zfr_rest' => array(
    'resource_metadata' => array(
        'drivers' => array(
            'application_driver' => array(
                'class' => 'ZfrRest\Resource\Metadata\Driver\AnnotationDriver',
                'paths' => array(__DIR__ . '/../src/Application/Entity')
            )
        )
    ),
)
```

This will load all metadata defined in Application/Entity folder. Typically, each of your module that contains mapping
should have this (with a *unique* key of course!).


### Set the routes

Currently, if you type "/users" in your browser, nothing happens. This is because we need to define *entry points*
for our simple API.

Let's add the following route:

```php
'router' => array(
    'routes' => array(
        'users' => array(
            'type'    => 'ResourceGraphRoute',
            'options' => array(
                'route'    => '/users',
                'resource' => 'Application\Repository\UserRepository'
            )
        ),
    )
)
```

We use a new route that is brought by ZfrRest, called: ResourceGraphRoute. This route automatically "discovers"
associations based on your mapping.

Here, we say that the entry point to the route "users" is "/users". Because we exposed the association "tweets", this
simple route automatically opens the following routes: "/users", "/users/:user_id", "/users/:user_id/tweets" and
"/users/:user_id/tweets/:tweet_id".

The "resource" key in the options most often will be a repository (or anything that implements the interface
`Doctrine\Common\Persistence\ObjectRepository`), or anything that implements `Doctrine\Common\Collections\Selectable`
interface.

If you are a Doctrine ORM user, this means you must add a factory to get the User repository, like this:

```php
'service_manager' => array(
    'factories' => array(
        'Application\Repository\UserRepository' => function($sl) {
            $objectManager = $sl->get('doctrine.entitymanager.orm_default');
            return $objectManager->getRepository('Application\Entity\User');
        }
    )
)
```

#### Filtering

The route will automatically filter collections and make the query, thanks to the Selectable Doctrine API. Optionally,
you can filter even more your queries by adding GET parameters. For instance: "/users?login=bakura". The route
will iterate through those parameters and, if the entity contains such parameters, it will filter accordingly. Unknown
parameters will simply be ignored.


### Define the controllers

Last step: let's create our controllers. Each of your controllers must extend ZfrRest\Mvc\Controller\AbstractRestfulController.

To allow a specific HTTP verb, your task is simply to define a method whose name is the HTTP verb. For instance, if you
want the UserController to respond to GET and DELETE methods, just write "get" and "delete" method. Any other method (PUT,
POST...) will throw a 403 error (Method Not Allowed).

By default, ZfrRest controller supports four methods: GET, PUT, POST and DELETE. We'll see later how to add support
for more methods, and how we can override basic assumptions of ZfrRest.

For every of those four methods, here is what you can expect:

* get($resource, ResourceMetadataInterface $resourceMetadata)
* delete($resource, ResourceMetadataInterface $resourceMetadata)
* put($resource, ResourceMetadataInterface $resourceMetadata)
* post($resource, $object, ResourceMetadataInterface $resourceMetadata): in this case, the resource is the collection
into which the object is inserted, while the object is the object to insert.

What is great about that, is that because ZfrRest already fetches the object, you can typehint each methods. For
instance, let's define the UserController and UserListController:


```php
namespace Application\Controller;

use ZfrRest\Mvc\Controller\AbstractRestfulController;

class UserController extends AbstractRestfulController
{
    public function get(User $user, ResourceMetadataInterface $metadata)
    {
    }

    // You can omit $metadata if yu don't need it
    public function delete(User $resource)
    {
    }

    public function put(User $user, ResourceMetadataInterface $metadata)
    {
    }
}
```

```php
namespace Application\Controller;

use ZfrRest\Mvc\Controller\AbstractRestfulController;

class UserListController extends AbstractRestfulController
{
    public function get(Paginator $users, ResourceMetadataInterface $meatadata)
    {
    }
}
```

Notice the "put" method in the UserController. Because of the mapping we defined earlier (input filter and
hydrator), the controller automatically extracted data, validate it and hydrate so that your controller only
receive the final object, ready to be saved by your service layer. If validation fails, it automatically
returns a 400 error, with the validation messages that failed.

Everything is optional. So if you want to receive data as an array instead, and validate data manually, you can
do it by configuring ZfrRest. Copy the config/zfr_rest.local.php.dist file into your config/autoload folder, and
just disable "auto_validate" and "auto_hydrate".


#### How to override and add new verbs

Internally, when a request is dispatched to a controller, ZfrRest does two things:

1. It calls a "handler". Handler method are like this: handle$verbMethod (handlePutMethod, handlePostMethod...). For
instance, handlePutMethod defined in AbstractRestfulController create the input filter, the hydrator... Each handler
receives the resource, and the resource metadata.
2. It then calls the method in your controller, like put, get... Most of the time, your controller only defines those
functions.

If you want to add support for new verbs or override the default handler, create a new abstract controller:

```php
namespace Application\Controller;

use ZfrRest\Mvc\Controller\AbstractRestfulController;

class AbstractCustomController extends AbstractRestfulController
{
    /**
     * Add support for a new method not handled by default
     */
    public function handlePatchMethod($resource, ResourceMetadataInterface $metadata)
    {
        // Do stuff...
        return $this->patch($parameters);
    }

    /**
     * Override put handler.
     */
    public function handlePutMethod($resource, ResourceMetadataInterface $metadata)
    {

    }
}
```

Now, you can extend this new controller, and simply define the "patch" method:

```php
namespace Application\Controller;

use ZfrRest\Mvc\Controller\AbstractRestfulController;

class UserController extends AbstractCustomController
{
    public function patch($parameters)
    {
    }
}
```

### Listeners

ZfrRest registers various listeners that can be activated/deactivated.
