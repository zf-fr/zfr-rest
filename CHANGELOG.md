# CHANGELOG

## 0.3.4

* ZfrRest now supports coalesce filtering for "hasMany" or "findMany" requests type through the new, optional
`enable_coalesce_filtering` module option. If enabled, ZfrRest will be able to respond to queries like
/customers?ids[]=5&ids[]=64, where `ids` is a configurable primary key name.

## 0.3.3

* Fix an issue with camelCased associations when rendering a resource

## 0.3.2

* Added support for OneToOne association (assuming you have a "User" entity with a OneToOne association to
a "Card" entity, you can now do a POST request like "/users/4/card")

## 0.3.1

* ZfrRest now returns input errors correctly if no data was given in the body

## 0.3.0

* Association mapping can now accept one new property: `collectionController`. It allows to map a specific
association resource to a specific controller, instead of using the target entity mapping.
* Add a doc section about optimizing ZfrRest for performance
* Nested input filters are now supported when errors occur on POST on PUT

## 0.2.3

* Associations can now have an extraction strategy set to `PASS_THRU`. This allows a parent hydrator to manually
renders an association, and let the renderers reuse this result for the given association.
* `paginatorWrapper` controller plugin now supports the resource data to be a plain PHP array.

## 0.2.2

* `routable` metadata is now correctly taken into account by the router, so it won't route to the associated
resource if the parameter is set to false.

## 0.2.1

* HttpExceptionListener now stops propagation if it can handle a specific exception.

## 0.2.0

* [FEATURE] You can now override the `getInputFilter` method in your controllers so that you can specify custom
logic like setting validation groups depending on authorization, HTTP method...

## 0.1.0

* Complete refactor, initial release (code cleaning, documentation...)

## 0.0.2

* Exclude request base path from path

## 0.0.1

* First release
