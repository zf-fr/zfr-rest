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
lines in the `zfr_rest.global.php` file.

## How to deal with custom Accept/Content-Type mime types ?

By default, ZfrRest comes bundled with native support for JSON. However, you may want to use your own mime types, like
"application/vnd-user-json". For this to work, you need to do two things:

* Add a decoder that will map a mime-type to a decoder. It is used among other to extract data from body in
POST and PUT methods.
* Add a model that will map a mime-type to a view model instance. It is used for ZfrRest to automatically output
an object (eg. a User instance).

Here is how you would do it:

```php
return array(
    'zfr_rest' => array(
        'models' => array(
            'invokables' => array(
                'application/vnd-user-json' => 'Zend\View\Model\ViewModel'
            )
        ),

        'decoders' => array(
            'invokables' => array(
                'application/vnd-user-json' => 'ZfrRest\Factory\JsonDecoderFactory'
            )
        )
    )
);
```

By default, ZfrRest map the following models:

* 'text/html' to 'Zend\View\Model\ViewModel'
* 'application/xhtml+xml' to 'Zend\View\Model\ViewModel'
* 'application/json' to 'Zend\View\Model\JsonModel'
* 'application/javascript' to 'Zend\View\Model\JsonModel'

And the following decoders:

* 'application/xml' to 'Symfony\Component\Serializer\Encoder\XmlEncoder'
* 'application/json' to 'ZfrRest\Factory\JsonDecoderFactory'
* 'application/javascript' to 'ZfrRest\Factory\JsonDecoderFactory'

> Note that although we have a decoder for Xml, ZfrRest currently does not fully support Xml because Zend Framework 2
does not have any XmlModel as of today.


## How to send JSON by default?

If you don't plan on writing any view templates for your controllers, it might be a good idea to
output `JSON` data by default. This can be accomplished by configuring ZfrRest models:


```php
return array(
    'zfr_rest' => array(
        'models' => array(
            'invokables' => array(
                // always send JSON, even when testing in the browser
                '*/*'                    => 'Zend\View\Model\JsonModel',
                'text/html'              => 'Zend\View\Model\JsonModel',
                'application/xhtml+xml'  => 'Zend\View\Model\JsonModel',
            )
        ),
    )
);
```

## How to solve "Unable to render template" errors?

If you see this error:
> Zend\View\Renderer\PhpRenderer::render: Unable to render template "application/item-list"; resolver could not resolve to a file

... it means that you're probably trying to access REST urls in your browser. Your browser by 
default expects html, so ZfrRest tries to find and render view template for your RestfulController.

In this case, you can either:

   1. Provide view templates for your RestfulControllers (i.e. create `view/script/application/item-list.phtml`)
   2. Change the default output format (i.e. to JSON, as described above)
