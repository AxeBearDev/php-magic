# PHP Magic

This PHP package uses magic methods to provide a composable way to add functionality to your classes via custom attributes.

## Installation

```bash
composer require spleenboy/php-magic
```

## Scripts
  
- `composer test`: Test with Pest
- `composer cli`: Open a Pysch shell with the package loaded

## What's in the Box?

This package provides powerful utilities for reusing code and adding functionality to your classes. Under the hood, it takes advantange of [PHP's magic methods](https://www.php.net/manual/en/language.oop5.magic.php) to provide a composable way to add functionality to your classes via custom [attributes](https://www.php.net/manual/en/class.attribute).

This package uses magic methods to add members that aren't exclicitly defined in your classes. Be sure to add type hints to your class. Otherwise, your IDE of choice won't give you hints about the `@property` or `@method` members you've added to your class.

### [Transforms](docs/transforms.md)
Apply changes to class properties when they are set or retrieved.

### [Fluency](docs/fluency.md)
Add fluent methods for class properties so your code can flow elegantly.

### [Track Changes](docs/track-changes.md)
Track changes to protected class properties in a class.