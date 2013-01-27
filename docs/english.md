Documentation
=============


Quick start
-----------

This section's goal is to quickly understand how ZfrRest works. It assumes that you are using Doctrine 2 library, as ZfrRest
heavily uses mapping information that automatically comes for free for any Doctrine ORM/ODM users. If you are not using
Doctrine, please refer to the following section to know which interfaces you need to implement to take advantage of ZfrRest.

This quick start also assumes that you have basic understanding of REST principles.

### Resources

TODO

#### From annotations

TODO

#### From PHP config files

TODO


### Routes

TODO


### Controllers

TODO


Exceptions
----------

ZfrRest comes with a listener, ``ZfrRest\Mvc\HttpExceptionListener``, that automatically sets the appropriate HTTP status
responde code and message, whenever a ``ZfrRest\Http\Exception\AbstractHttpException`` is thrown in your controller. This
allow you to easily set response code without messing yourself with the Response object. Note that, thanks to another listener, ZfrRest is smart enough to format the error message according the the ``Accept`` header.

### Available exceptions

ZfrRest comes bundled with the most common HTTP status code:

**Client errors**

* BadRequestException: code 400
* ConflictException: code 409
* ForbiddenException: code 403
* GoneException: code 410
* MethodNotAllowedException: code 405
* NotFoundException: code 404
* UnauthorizedException: code 401

**Server errors**

* InternalServerErrorException: code 500
* NotImplementedException: code 501
* ServiceUnavailableException: code 503

> Note: if you throw an ``UnauthorizedException``, ZfrRest will automatically add the ``WWWAuthenticate`` header to the HTTP response, as recommended by [RFC 2617](http://www.ietf.org/rfc/rfc2617.txt).


### Usage

To take advantage of ZfrRest exceptions, just throw an exception in your controller:

```php
public function get(User $user)
{
	if ($user === null) {
		throw new \ZfrRest\Http\Exception\Client\NotFoundException();
	}
}
```

Each exception comes with a default message, but you can easily override by giving another message as the first parameter
of the constructor:

```php
public function get(User $user)
{
	if ($user === null) {
		throw new \ZfrRest\Http\Exception\Client\NotFoundException('Sorry, no user could be found!');
	}
}
```

If you want to throw an exception that is not bundled by default with ZfrRest, you can use the generic ``ClientException``
and ``ServerException`` exceptions:

```php
public function get(User $user)
{
	throw new \ZfrRest\Http\Exception\ServerException(505, 'The server does not support this HTTP protocol version');
}
```


Resources
---------

### What is a resource ?

TODO

### From annotations

TODO

### From PHP config files

TODO



Routes
------

TODO


Controllers
-----------

ZfrRest uses a custom ``Zend\Mvc\Controller\AbstractController`` subclass. To be able to use ZfrRest, simply extends
``ZfrRest\Mvc\Controller\AbstractRestfulController`` instead of the standard ZF 2 Restful controller. Then, simply write
your methods (**without** action appended !) that match the HTTP verb method. For instance, here is a simple User controller
that only support GET/DELETE methods:

```php
namespace Application\Controller;

use ZfrRest\Mvc\Controller\AbstractRestfulController;

class UserController extends AbstractResftulController
{
	public function get(User $user)
	{
	}
	
	public function delete(User $user)
	{
	}
}
```


MIME-Type to models
-------------------

Some listeners, like ``ZfrRest\Mvc\View\Http\SelectModelListener``, map a MIME-Type (extracted from the Accept header, for
instance) to a ``ModelInterface`` instance. This mechanism is used to automatically convert data to the desired output
format based on some headers values.

Therefore, we provide a plugin manager called ``ZfrRest\View\Model\ModelPluginManager`` that do this mapping. ZfrRest
comes with some mapping out-of-the box:

* 'text/html' 			    => 'Zend\View\Model\ViewModel'
* 'application/xhtml+xml'  => 'Zend\View\Model\ViewModel'
* 'application/json'       => 'Zend\View\Model\JsonModel'
* 'application/javascript' => 'Zend\View\Model\JsonModel'

> Note: XML is not yet supported because ZF 2 does not have XmlModel. Feel free to patch ZF 2 and we'll be happy to add support in ZfrRest ;-) !

### How to add your own MIME-Type => models

If you have custom MIME-Type that you want to map to a ModelInterface (for instance, ``application/x-experimental-json`` to
JsonModel), you have two main ways to do it: 

#### Using the config file

In your ``module.config.php`` file, add the following under the ``models`` key to add a new decoder/encoder:

```php
return array(
	'models' => array(
		'invokables' => array(
			'application/x-experimental-json' => 'Zend\View\Model\JsonModel'
		),
	),
);
```

#### In the Module.php file

In your ``Module.php`` file, implement the ``ZfrRest\ModuleManager\Feature\ModelProviderInterface``:

```php
use ZfrRest\ModuleManager\Feature\ModelProviderInterface;

class Module implements ModelProviderInterface
{
	public function getModelConfig()
	{
		return array(
			'invokables' => array(
				'application/x-experimental-json' => 'Zend\View\Model\JsonModel'
			)
		);
	}
}
```


Decoders and encoders
---------------------

Internally, ZfrRest uses the Symfony\Serializer library to handle the various decoding and encoding processes. This is
used in many locations in the module. For instance, decoders are used to parse the request body, while encoders are used
to output the data returned from controllers.

ZfrRest offers two plugin managers, ``ZfrRest\Serializer\DecoderPluginManager`` and ``ZfrRest\Serializer\EncoderPluginManager``. Many classes in the module pulls those managers from the service locator to perform various tasks.

### Built-in encoders/decoders

As we are using Symfony\Serializer component, the following MIME-Type are mapped to those encoders/decoders:

**Decoders**

* 'application/json' => 'Symfony\Component\Serializer\Encoder\JsonDecode'
* 'application/javascript' => 'Symfony\Component\Serializer\Encoder\JsonDecode'
* 'application/xml' => 'Symfony\Component\Serializer\Encoder\XmlEncoder'

**Encoders**

* 'application/json' => 'Symfony\Component\Serializer\Encoder\JsonEncode'
* 'application/javascript' => 'Symfony\Component\Serializer\Encoder\JsonEncode'
* 'application/xml' => 'Symfony\Component\Serializer\Encoder\XmlEncoder'



### Adding your own encoders/decoders

Often, you will need to map custom MIME-Type to specific encoders/decoders. For instance, let's say that you want the
``application/x-experimental-json`` MIME-Type to map to a JSON encoder/decoder. You have two main ways to do it:

#### Using the config file

In your ``module.config.php`` file, add the following under the ``decoders``/``encoders`` key to add a new decoder/encoder:

```php
return array(
	'decoders' => array(
		'invokables' => array(
			'application/x-experimental-json' => 'Symfony\Component\Serializer\Encoder\JsonDecode'
		),
		
		'factories' => array(
			'application/x-complex-json' => 'MyModule\Service\ComplexJsonDecoderFactory'
		)
	),
	
	'encoders' => array(
		'invokables' => array(
			'application/x-experimental-json' => 'Symfony\Component\Seriaizer\Encoder\JsonEncode'
		)
	)
);
```


#### In the Module.php file

In your ``Module.php`` file, implement the ``ZfrRest\ModuleManager\Feature\DecoderProviderInterface`` and/or
``ZfrRest\ModuleManager\Feature\EncoderProviderInterface`` interfaces:

```php
use ZfrRest\ModuleManager\Feature\DecoderProviderInterface;
use ZfrRest\ModuleManager\Feature\EncoderProviderInterface;

class Module implements DecoderProviderInterface, EncoderProviderInterface
{
	public function getDecoderConfig()
	{
		return array(
			'invokables' => array(
				'application/x-experimental-json' => 'Symfony\Component\Serializer\Encoder\JsonDecode'
			)
		);
	}
	
	public function getEncoderConfig()
	{
		return array(
			'invokables' => array(
				'application/x-experimental-json' => 'Symfony\Component\Serializer\Encoder\JsonEncode'
			)
		);
	}
}
```



Listeners
---------

Various listeners are used throughout this module to provide, for instance, automatic serialization of controller output
according to the format specified in ``Accept`` header. All of those listeners can be disabled/enabled and overrided.

### ZfrRest\Mvc\HttpExceptionListener

This listener listens to the ``MvcEvent::EVENT_DISPATCH_ERROR`` event with a priority of 100. It is responsible to catch any
exception extending from ``ZfrRest\Http\Exception\AbstractHttpException`` thay is thrown in your controllers, so that it can
prepare the Response correctly, as well as return data that will be converted to a ViewModel by another listener.

#### How to enable/disable this listener ?
 
This listener is *enabled by default*, but can be disabled by setting the ``register_http_exception_listener`` option to false.


### ZfrRest\Mvc\View\Http\SelectModelListener

This listeners listens to the following events:

* ``MvcEvent::EVENT_DISPATCH_ERROR`` with a priority of 80.
* ``MvcEvent::EVENT_DISPATCH`` with a priority of -60.

The goal of this listener is to automatically encode the returned data of your controller to another format based on the
HTTP Accept header. This means that, to take advantage of this feature, you only must return array from your controllers. If
you explicitely send a concrete ``ModelInterface`` instance (like ``ViewModel`` or ``JsonModel``), it will force the output
to this format.

For instance, if the highest priority value in Accept header is ``application/json``, the following action wil automatically
serialize the user object to Json:

```php
public function get(User $user)
{
	return array(
		'user' => $user
	);
}
```

Note that the same mechanism of automatic encoding will work too when you send an exception, as this listener listens to the
``MvcEvent::EVENT_DISPATCH_ERROR`` event too.

#### How to enable/disable this listener ?
 
This listener is *enabled by default*, but can be disabled by setting the ``register_select_model_listener`` option to false.


### ZfrRest\Mvc\HttpMethodOverrideListener

This listener listens to the ``MvcEvent::EVENT_DISPATCH`` event with a priority of 200.

Sometimes, you are limited about which HTTP method you are allowed to send. For instance, some very old browsers or some
companies' proxies only allow POST and GET methods. You may also want to send DELETE method when submitting a form (which
only support GET and POST methods).

A common work-around for this is to send a specific header, ``X-HTTP-Method-Override``, whose value will be the HTTP method.
ZfrRest completely supports this header thanks to this listener. The only thing you need is adding this header in your Ajax
request, and ZfrRest will automatically update the HTTP method according to this header.

#### How to enable/disable this listener ?
 
This listener is *disabled by default*, but can be enabled by setting the ``register_http_method_override_listener`` option to true.


### How to override a listener?

Each listener is pulled from the service locator. This means that you can easily override them if you are not satisfied by
the default behaviour. For instance, if you want to override ``ZfrRest\Mvc\HttpExceptionListener``, just replace the original
invokables by yours in any ``module.config.php`` file (of course, the module has to be included AFTER ZfrRest in the
``application.config.php``):

```php
	return array(
		'service_manager' => array(
			'invokables' => array(
				'ZfrRest\Mvc\HttpExceptionListener' => 'MyModule\Mvc\AnotherHttpExceptionListener'
			)
		)
	);
```


Parsing Request and Response objects
------------------------------------

You sometimes need to parse Request and Response objects according to various HTTP headers (``Content-Type``…). ZfrRest
comes with some parsers out-of-the-box.

All those parsers work with a ``ZfrRest\Serializer\DecoderPluginManager`` object, that map a MIME-Type to a specific
decoder used by the parser. To see how to add your own MIME-Type => decoder mapping, please refer to this section.


### BodyParser

This parser will parse the Body content of the Request and will return the data in the format specified in the MIME-Type in
the ``Content-Type`` header. To use it, simply instantiate it from the service locator:

```php
// We assume content of the request have {"foo": bar} and that the Content-Type is any
// MIME-Type that is mapped to a JsonDecode:
$result = $serviceLocator->get('ZfrRest\Http\Parser\Request\BodyParser')->parse($request);

var_dump($result); // Will output array('foo' => 'bar)
```


How to use ZfrRest if you are not using Doctrine 2 ?
----------------------------------------------------

If you are not a user of Doctrine 2, you will need a little more work to completely take advantage of all the ZfrRest
features. Basically, you will need to expose some information about your entities through mapping information (associations,
fields…). Furthermore, your mappers will need to implement some classes that allow filtering collections.

> In the future, we plan to make some integration with Zend\Db so that you have less work to do manually.

TODO