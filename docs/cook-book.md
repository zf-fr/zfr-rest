# Cook-book

## How to override and add new verbs

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

## How to prevent ZfrRest from auto-validating and/or auto-hydrating

By default when POSTing or PUTing, ZfrRest automatically validate the data using the input filter defined in the
mapping, and hydrate it using the hydrator defined in the mapping (it defaults to `DoctrineModule\Stdlib\Hydrator\DoctrineObject`
for single resource, or `ZfrRest\Stdlib\Hydrator\PaginatorHydrator` for collections).

However, you may want to do it yourself because you have very specify requirements. You can do it by disabling the
option `auto_hydrate` and `auto_validate` options in the config file (the keys lie under the `controller_behaviours` key).

## Listeners

ZfrRest registers various listeners that can be activated/deactivated in the config file. Just uncomment specific
lines in the `zfr_rest.local.php` file.
