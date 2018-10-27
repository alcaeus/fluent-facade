# fluent-facade
🏡 Library to create fluent interfaces for classes that don't support them

## Installation

To install using composer, run

```bash
$ composer require alcaeus/fluent-facade
```

## Usage

To wrap an object that does not provide a fluent interface, use the static 
`ClassWrapper::wrap` helper, then use the class as if it provided a fluent
interface:

```php
use Alcaeus\Fluent\ClassWrapper;

ClassWrapper::wrap($object)
    ->doSomething()
    ->doSomethingElse();
```

### Getters and other return values

Any non-object return value from a method is ignored and discarded. If a method
returns an object, this object is automatically wrapped and returned, allowing
nested calls. You can end this nesting by calling the `end()` method:

```php
use Alcaeus\Fluent\ClassWrapper;

ClassWrapper::wrap($object)
    ->doSomething()
    ->getNestedObject()
        ->doSomethingElse()
    ->end()
    ->getOtherNestedObject()
        ->doYetAnotherThing();
```

Note that it's not possible to call `end()` on the root object that was wrapped.

### Public properties

If your class contains public properties that contain objects, you can access
them and get a wrapped instance of the original object. Properties that contain
non-object values will cause an exception to be thrown.

```php
use Alcaeus\Fluent\ClassWrapper;

ClassWrapper::wrap($object)
    ->doSomething()
    ->nestedObject
        ->doSomethingElse()
    ->end()
    ->otherNestedObject
        ->doYetAnotherThing();
```
