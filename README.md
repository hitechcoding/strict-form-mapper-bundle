
# Strict form mapper bundle

This bundle adds useful options to your forms, eliminates magic access (get* and set*), turns exceptions into validation errors...


## Install

Via Composer

``` bash
$ composer require htc/strict-form-mapper-bundle
```

## Intro

Static analyses is of critical importance for complex applications; it allows you to catch errors quickly and changing method names is safe.

PHP7 gave us strict files and greatly improved type declarations, bringing it closer to languages like Java. 
But if you use these new features in your entities, integrated form mapper will give you problems.


This gave rise to idea of DTOs. While I **do** agree that should be the best way of working with forms, DTO mapping will become nightmare if you have complex forms with collections.

So as long as you don't have ``$em->flush()`` lurking somewhere in form events (and you shouldn't), with this bundle you can safely work with your strictly typed entities.


---

##### Examples demonstrated here are using upcoming property hinting of PHP7.4, Doctrine annotations are omitted

## Usage

- [Factory](/docs/factory.md)
- [Accessors](/docs/accessors.md)
- [Collections without parent](/docs/collections_without_parent.md)
