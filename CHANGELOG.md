# CHANGELOG

## 0.2.3

* Assocations can now have an extraction strategy set to `PASS_THRU`. This allows a parent hydrator to manually
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
