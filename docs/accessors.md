When you add field to form, Symfony will use ``get*`` and ``set*`` prefix to access it:


```php
$builder->add('category', EntityType::class);

```

This forces you to write following methods:

```php
class Product
{
    public function getCategory(): Category
    {
        return $this->category;
    }
    
    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }
}
```

This brings few problems; 
- you are forced to ``get*/set*`` naming convention in your classes
- even though your logic requires instance of Category, not using nullable types will throw TypeError exception
- renaming method from ``setCategory`` to ``changeCategory`` **will** break your form
- both methods will be reported as ``unused`` in your IDE or by tools that detect them

---
Instead, use this (upcoming arrow functions used for better readability):

```php
$builder->add('category', EntityType::class, [
    'get_value' => fn(Product $product) => $product->getCategory(),
    'update_value' => fn (Category $category, Product $product) => $product->changeCategory($category),
    'write_error_message' => 'You must select category from dropdown.',
]);
```

Your entity can now be:

```php
class Product
{
    public function getCategory(): Category
    {
        return $this->category;
    }
    
    public function changeCategory(Category $category): void
    {
        $this->category = $category;
    }
}
```

The value of ``write_error_message`` will be rendered as translated field error when ever TypeError exception is thrown. If you leave it empty, you must provide ``NotNull`` constraint that will deal with it.

With these 2 callables, you have solved the problems above. Your entity is now ready to be used in strict mode.

---


If the field is a collection, you can use adders and removers, instead of setter. In this case, it would be many2many relation between Category and Product.

Our Product entity is this:

```php
class Product
{
    private $categories;
    
    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }
    
    /** @return Category[] */
    public function getCategories(): array
    {
        return $this->categories->toArray();
    }
    
    public function addCategory(Category $category): void
    {
        $this->categories->add($category);
    }
    
    public function removeCategory(Category $category): void
    {
        $this->categories->removeElement($category);   
    }
}
```

The form:

```php
$builder->add('categories', EntityType::class, [
    'multiple' => true,
    'get_value' => fn (Product $product) => $product->getCategories(),
    'add_value' => fn (Category $category, Product $product) => $product->addCategory($category),
    'remove_value' => fn (Category $category, Product $product) => $product->removeCategory($category),
    'write_error_message' => 'You must select category from dropdown.',
]);
```


Using callables also allows you to pass extra information to methods. Unrealistic example could be:

```php
class Product
{
    //...
    public function removeCategory(Category $category, bool $force = false): void
    {
        if ($force) {
            $this->categories->removeElement($category);   
        }
    }
}
```

and form:
```php
'remove_value' => fn (Category $category, Product $product) => $product->removeCategory($category, true),
```

Another advantage is that it allows you to work with collections without parent class. Check [documentation for that](collections_without_parent.md)
