[Best practices](https://symfony.com/doc/current/best_practices/forms.html#validation) for form validation says to put constraints on class level: 

```php
class Product
{
    /** @Assert\NotNull */
    private Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
}
```

In our strict model, this code wouldn't make any sense; we don't even allow null for $category so ``NotNull`` constraint will never be triggered. Remember; our business rule was that Product **must** have a Category and the only proper way to make it is via constructor.

Form validation works by validating both ``constraints`` key and underlying class itself. In this case, we wouldn't have that entity because ``factory`` would fail if submitted category was null. And that also means; no validation error.

---

It is easy to solve it; just add constraint to form class:

```php
$builder->add('category', EntityType::class, [
    'get_value' => fn(Product $product) => $product->getCategory(),
    'update_value' => fn (Category $category, Product $product) => $product->changeCategory($category),
    'constraints' => [
        new NotNull(),
    ],
]);
```

but again; boring and repeatable job that is easy to forget. Instead, bundle helps you; if you don't put ``NotNull`` in your ``constraints`` key, it will do it for you and trigger it when submitted value is null.

---
Keep in mind that this is **only** for ``NotNull`` and **only** if there is no underlying data and **only** if TypeError exception is thrown. It would be very rare case you need to worry about it, but is convenient tool to avoid some WTF situations.


