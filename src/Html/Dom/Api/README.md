# DOM Interfaces

The word « specification » (or « spec ») refers to the [WHATWG DOM specification](https://dom.spec.whatwg.org).

Interfaces in this directory whose name starts with `Dom` are interfaces for their corresponding DOM nodes,
as implemented by the PHP DOM extension.

Interfaces in this directory whose name starts with `Html` extend their corresponding `Dom` interfaces.
They are here to add features that are in the specification but are not implemented by PHP's DOM extension.

Other interfaces in this directory map to the « mixins » defined by the spec.

As there's no such thing as a read-only attribute in PHP,
Interfaces are annotated with PHPDoc's `@property` or `@property-read` tags.
For each `@property-read` annotation, there should be a corresponding getter method.
For each `@property` annotation, there should be a corresponding getter and a corresponding setter method.
For example:
```php
<?php
/**
 * @property-read int $foo
 * @property string $fooBar
 */
interface Example {
    public function getFoo(): int;
    public function getFooBar(): string;
    public function setFooBar(string $value): void;
}
```
Implementing classes must override the « magic » `__get` and `__set` methods to map the getters and setters
to the corresponding interface properties.
