# SchemaBuilder

A simple library to generate [Schema.org](http://schema.org).

## Installation

This library requires PHP 5.4 or later.

It is recommended that you install this library using Composer.

## Dependencies

SchemaBuilder relies upon and requires the following dependencies:

* `danielstjules/stringy` - A PHP string manipulation library.

## Getting Started

### Build Schema.org Object

Start by instantiating your primary Schema.org object with the **type** of the object.

For example, to create a [Book](http://schema.org/Book):

```php
<?php
$book = new NYPL\SchemaBuilder\Schema('Book');
```

Then, set the properties of the object wih the property **name** and **value**:

```php
<?php
$book->addProperty('name', 'Le concerto');
$book->addProperty('author', 'Ferchault, Guy');
```

The property can also contain other Schema.org objects:

```php
<?php
// First, build the Offer object
$offer = new Schema('Offer');
$offer->addProperty('availability', 'http://schema.org/InStock');
$offer->addProperty('serialNumber', 'CONC91000937');
// Then, set the "offers" property on the Book object with the Offer object
$book->addProperty('offers', $offer);
```

### Output Schema.org as Microdata

When you have finished building your Schema.org object, output it in two ways:

#### 1. Properties Only

To output without a HTML wrapper use the `outputMicrodata` method with or without a property name.

```
// Output an Object
<div <?php $book->outputMicrodata(); ?> itemid="#record">
// Output an Object's property
<h1 <?php $book->outputMicrodata('name'); ?>><?php $book->outputProperty('name'); ?></h1>
```

If you do **not** specify a property name, microdata to describe the primary object will be generated.

Outputs:

```html
<div itemscope itemtype="http://schema.org/Book" itemid="#record">
<h1 itemprop="name">Le concerto</h1>
```

#### 2. Wrapped Properties

To output with a property with a HTML wrapper specify the name of the property and the wrapper that you want to use:

```
<?php $book->outputMicrodata('additionalType', 'link'); ?>
<?php $book->outputMicrodata('name', 'h3'); ?>
```

Outputs:

```html
<link itemprop="additionalType" href="http://schema.org/Product">
<h3 itemprop="name">Le concerto</h3>
```

## Tests

From the project directory, tests can be ran using `phpunit`.
