
# Strict form mapper bundle

This bundle adds useful options to your forms, eliminates magic accessors (get* and set*), turns TypeError exceptions into validation errors...


## Install

Because this bundle is still under development, it is not published to packagist. To install it, add this to composer.json

``` json
{
    "minimum-stability": "dev",
    "prefer-stable": true,
    "require": {
        "hitechcoding/strict-form-mapper-bundle": "dev-master"
    },
    "repositories": [
        {
            "type": "github",
            "url": "https://github.com/hitechcoding/strict-form-mapper-bundle"
        }
    ]
}
```

## Intro

Static analyses is of critical importance for complex applications. With tools like [phpstan](https://github.com/phpstan/phpstan) it allows you to catch errors quickly and changing method names is safe.

PHP7 gave us strict files and greatly improved type declarations, bringing it closer to languages like Java. 
But if you use these new features in your entities, integrated form mapper will give you problems.


This gave rise to idea of DTOs. While I **do** agree that should be the best way of working with forms, DTO mapping will become nightmare if you have complex forms with collections.

So as long as you don't have ``$em->flush()`` lurking somewhere in form events (and you shouldn't), with this bundle you can safely work with your strictly typed entities.

## Motive
The idea came from [rich forms bundle](https://github.com/sensiolabs-de/rich-model-forms-bundle) but I find it not strict enough. Make sure to check it as well, it does provide some features that are not yet supported in this bundle (but will be).

---

## Usage

- [Factory](/docs/factory.md)
- [Accessors](/docs/accessors.md)
- [Collections without parent](/docs/collections_without_parent.md)
- [Not null validation](/docs/validation.md)

###### Notice
All examples shown use new PHP7.4 features like typed properties and arrow functions, for readability reasons. Bundle itself still requires only PHP7.1. 
