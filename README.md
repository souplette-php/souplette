# souplette

[![Codecov](https://img.shields.io/codecov/c/github/souplette-php/souplette?logo=codecov&style=for-the-badge)](https://codecov.io/gh/souplette-php/souplette)

PHP implementations for various whatwg specifications:

* DOM
* HTML parsing and serialization
* HTML sanitizer
* CSS selectors level 4

## Parsing

### Parsing HTML

```php
use Souplette\Souplette;
use Souplette\DOM\Document;

$html = file_get_contents('/path/to/file.html');
$doc = Souplette::parseHTML($html);
assert($doc instanceof Document);
```

## Serialization

### Serializing HTML documents

```php
use Souplette\Souplette;

$html = Souplette::serializeDocument($doc);
```

### Serializing HTML fragments

```php
$element = $document->getElementById('example');
$html = $element->outerHTML;
```

## Sanitizer API

### Sanitizing HTML documents

```php
use Souplette\HTML\Sanitizer\Sanitizer;
use Souplette\HTML\Sanitizer\SanitizerConfig;

// With the default configuration
$sanitized = Sanitizer::default()->sanitize($document);

// Using a custom configuration
$config = SanitizerConfig::create()
    ->blockElements('script', 'style');
$sanitized = Sanitizer::of($config)->sanitize($document);
```

### Sanitizing HTML fragments

#### Using `Element::setHTML()`

```php
$element = $document->getElementById('example');
// With the default configuration
$element->setHTML('<script>alert("pwnd!")</script>');
// Using a custom configuration
$config = SanitizerConfig::create()
    ->blockElements('script', 'style');
$element->setHTML('<script>alert("pwnd!")</script>', [
    'sanitizer' => Sanitizer::of($config),
]);
```

#### Using `Sanitizer::sanitizeFor()`

```php
use Souplette\HTML\Sanitizer\Sanitizer;

$html = '<script>alert("pwnd!")</script>';
$sanitized = Sanitizer::default()->sanitizeFor('form', $html);
```

## CSS selectors support

The Souplette DOM api supports the `querySelector` and `querySelectorAll` methods
on `Document` and `Element` instances.

Due to the non-dynamic nature of the Souplette DOM implementation (no scripting engine),
some selectors (like `:hover`, `:focus`, etc...) cannot be supported.

In some cases however the selector engine can use an approximation.
For example `input:disabled` will match an input element if either:
* the input element has a `disabled` attribute, or
* the input element is a descendant of a `fieldset` element with a `disabled` attribute.
