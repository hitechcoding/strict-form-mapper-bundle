Let's say you want to edit multiple products in the same form:

```php
class ComboType extends AbstractType
{
    private $em;

    public function __construct(EntityRepositoryInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $products = $this->em->getRepository(Product::class)->findAll();
        $data = [
            'products' => $products,
        ];

        $builder->add('products', CollectionType::class, [
            'entry_type' => ProductType::class,
            'constraints' => [
                new Valid(),
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'get_value' => function (array $data) {
                return $data['products'];
            },
            'add_value' => function (Product $product) {
                $this->em->persist($tag);
            },
            'remove_value' => function (Product $product) {
                $this->em->remove($product);
            },
            'write_error_message' => null,
        ]);
        
        $builder->setData($data);
    }
}
```
``write_error_message`` is set to null. If ``entry_type`` fails because its ``factory`` failed, we don't need duplicated error messages.


With default mapper, you would have to use some class as DTO and inject $em into it, just so you can call ``$em->persist()`` and ``$em->remove()`` when you add or remove a product. 

``add_value`` and ``remove_value`` will still receive $data as second parameter but we don't need to map it. 

