# UPGRADE GUIDE

## 1.0.0 (voltaire)

* `@REST\Association` has been renamed to `@REST\ExposedAssociation`
* Routing changes
  * Router can now be used in child-routes.
  * Routes of type `ResourceGraphRoute` **must not** contain a literal prefix.
  * For example, this route is invalid: `/api/v3/data/users` (it will never match)
  * Static (literal) parts of the route **must** now be defined separately as part of parent route, for example:

````php
// routes => array(
    // this will work with: /api/v3/data/users
    'rest-data' => array(
        'type'         => 'Literal',
        'options' => array(
            'route'    => '/api/v3/data'
        ),
        'child_routes' => array(
            'users'    => array(
                'type'    => 'ResourceGraphRoute',
                'options' => array(
                    'route'    => '/users',
                    'resource' => 'Application\Repository\UserRepository'
                )
            ),
        )
    )
````

* Configuration changes
  * `resource_metadata` key is now removed and all sub-keys have been moved to the main configuration array
    (i.e. `[zfr_rest][resource_metadata][cache]` becomes `[zfr_rest][cache]`
  * `cache` now expects a name of a service which will be loaded from ServiceManager. The service must
    implement `Metadata\Cache\CacheInterface`
