### The problem

For simplicity reasons, let's say that you have a Product entity which **must** belong to Category; i.e. it can never be null.

The correct way is to inject that value into constructor:


```php
class Product
{
    private Category $category;
    
    public function __construct(Category $category)
    {
        $this->category = $category;
    }
    
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
_Doctrine annotations are omitted._

And controller:

```php
/**
* @Route("/create")
*/
public function createProduct(Request $request): Response
{
    $form = $this->createForm(ProductType::class);
    if ($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
        $product = $form->getData();
        // persist, flush and redirect
    }

    return $this->render('...');
}
```

When there is no underlying data (entity), Symfony will look for [``empty_data``](https://symfony.com/doc/current/form/use_empty_data.html) callable in attempt to create it. So ProductType is this:

```php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder->add('category', EntityType::class, [
        'constraints' => [
            new NotNull(['message' => 'You must select category.']),
        ],
        ... 
    ]);
}

public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'empty_data' => function (FormInterface $form) {
            return new Product($form->get('category')->getData());
        },
    ]);
}
```


The problem is when submitted ``category`` is null; your constructor does not allow it and will throw TypeError exception.


You could solve it by removing dependency from constructor but then your return declaration must be:

```php
class Product
{
    private ?Category $category;
    
    public function getCategory(): ?Category
    {
        return $this->category;
    }
}
```

Now at beginning, we decided that product must **always** have a category but clearly, the code says it can be null. We are breaking rules just to make our form work. This is where you would have to use DTO to fix the problem.

---
So instead of ``empty_data``, let's use ``factory``:
```php
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'factory' => function (Category $category) {
             return new Product($category);
         },
        'factory_error_message' => 'Cannot create new instance of Product entity',
    ]);
}
```


Instead of Closure, you can also use ``[$object, $method]`` syntax:
```php
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'factory' => [$this, 'factory'],
    ]);
}

public function factory(Category $category): Product
{
    return new Product($category);
}
```
---

With this code, if TypeError exception is thrown (i.e. category is null), it will be converted to validation error with translated message from ``factory_error_message``. 
In most cases you would want to suppress it (``'factory_error_message' => null``) because error on ``category`` field will be enough.


You can still inject form object like for empty data; just typehint it with ``FormInterface`` (name and order is irrelevant).

#### Warning
The parameters of ``factory`` callable **must** be named as fields of your form, order is irrelevant. Otherwise, bundle can not know which form fields you want injected.

So far, we **only** replaced ``empty_data`` with ``factory``. To make our form fully working, you also need to set [accessors](/docs/accessors.md) to field and your entity can be safely used in forms.