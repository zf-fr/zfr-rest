# Quick start

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

### Configurate the mapping

In those examples, we are using annotations, so in order to ZfrRest to "read" the mapping, we must add a driver.
Drivers in ZfrRest work pretty the same way as for Doctrine. In your `module.config.php`, add the following:

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
information can be found [in the PHP mapping reference](/../php-mapping-reference.md).

[In next part](03-configuring-router.md), you are going to configure the router.
