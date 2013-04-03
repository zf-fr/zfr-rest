ZfrRest
=======

[![Build Status](https://travis-ci.org/zf-fr/ZfrRest.png?branch=master)](https://travis-ci.org/zf-fr/ZfrRest)

UPDATE 3rd april 2013 : a develop branch has been merged to master as convenience, because the PR was getting too massive
to handle. However, please note that the API may change completely and it's not means to development yet. Thanks !

A module for Zend Framework 2 that aims to simplify RESTful


## EXAMPLE OF THIS DEVELOP BRANCH

### Define mapping

Let's define two entities with some basic mapping. We can see basic Doctrine mapping and ZfrRest mapping. We map
every User to the Controller Application\Controller\Simple. We also expose the association "tweets". This opens
any urls like "/users/5/tweets" or "/users/5/tweets/1".

Every tweet is mapped to Application\Controller\Tweet (but can be overrided at association level by setting another
controller directly in the tweets property).

User.php:
```php
namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ZfrRest\Resource\Annotation as REST;

/**
 * @ORM\Entity
 * @REST\Controller(name="Application\Controller\User")
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

### Specify driver

Copy this into your module.config.php (for now please disable all the listeners, as well as decoders, encoders, models).

Here we tell the object_manager used internally, and where the mapping is (we could re-use the one already specified
for Doctrine).

```php
'zfr_rest' => array(
        /**
         * Select which listeners should be registered
         */
        'register_http_exception_listener'       => false,
        'register_select_model_listener'         => false,
        'register_http_method_override_listener' => false,

        'resource_metadata' => array(
            'object_manager' => 'doctrine.entitymanager.orm_default',

            'drivers' => array(
                'application_driver' => array(
                    'class' => 'ZfrRest\Resource\Metadata\Driver\AnnotationDriver',
                    'paths' => array(__DIR__ . '/../src/Application/Entity/User')
                )
            )
        ),

        /**
         * Decoders
         */
        //'decoders' => array(),

        /**
         * Encoders
         */
        //'encoders' => array(),

        /**
         * Models
         */
        //'models' => array()
)
```

### Set the routes

Here we create a new route `users` with a route whose type is ResourceGraphRoute. The route is the entry point. As
we allowed to expose association "tweets", this allow routes "/users", "/users/5", "/users/5/tweets", "/users/5/tweets/1".

The resource is the class of ONE single model.

```php
'router' => array(
    'routes' => array(

    'users' => array(
        'type'    => 'ResourceGraphRoute',
        'options' => array(
            'route'    => '/users',
            'resource' => 'Application\Entity\User'
            )
        ),
    )
)
```

### Create controller

The controllers must extend "ZfrRest\Mvc\Controller\AbstractRestfulController". The logic is as follow: the method
name comes by the HTTP verb (GET is mapped to get, DELETE is mapped to delete...). Furthermore, if the result is
Traversable, we append method by List.

This means that if we query "/users", it will go to the getList(Collection $users), while "/users/5" will go to get(User $user).

```php
namespace Application\Controller;

use ZfrRest\Mvc\Controller\AbstractRestfulController;

class UserController extends AbstractRestfulController
{
    public function get(User $resource)
    {
    }

    public function getList(Collection $users)
    {
    }
}
```

Every method will also receive a second parameter, which is a ResourceMetadataInterface. This object allows to
retrieve various information, like the hydrator used, which associations are exposed... So you could rewrite
your controllers like this if you need those information:

```php
namespace Application\Controller;

use ZfrRest\Mvc\Controller\AbstractRestfulController;

class UserController extends AbstractRestfulController
{
    public function get(User $resource, ResourceMetadataInterface $metadata)
    {
    }

    public function getList(Collection $users, ResourceMetadataInterface $metadata)
    {
    }
}
```


### More filter

The route also support query filtering. So you can do: "/users/5/tweets?title=info". This will automatically filter
the tweets from user 5 that has title = info.


### Thoughts ?
