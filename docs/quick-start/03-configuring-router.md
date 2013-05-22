# Quick start

## Configuring the router

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

![ZfrRest](/../img/zfr-rest-error-access-resource.png)

It throws an exception, which is normal. This is because we didn't write our controller. We are going to do that
in next part.

[In next part](/04-creating-controller.md), you are going to create the controllers.
