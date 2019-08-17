When you work with collections, [Symfony docs](https://symfony.com/doc/master/form/form_collections.html) reminds you of putting ``cascade={"persist"}`` on owning side. The reason is that Doctrine will take care about which element in collection needs to be removed or saved to database and users don't have to do that manually.

In most cases this will not be a problem. But if you have really complex app where lots of entities are related to one (in their example Task entity), you would end with lots of ``add*/remove*`` methods that will be used only in forms and nowhere else.

Another problem is that [Doctrine best practices](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/best-practices.html) says to avoid bidirectional relations when possible. 

You can avoid that by using adders and removers on form level instead. For example, let's say you want to edit multiple products in one form:

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

            // extra keys for bundle
            'get_value' => fn (array $data) => $data['products'],
            'add_value' => fn (Product $product) => $this->em->persist($product),
            'remove_value' => fn (Product $product) => $this->em->remove($product),
            'write_error_message' => null,
        ]);
        
        $builder->setData($data);
    }
}
```
###### Notes
``write_error_message`` is set to null. If child ProductType::class fails because its ``factory`` failed (i.e. doesn't pass validation), we don't need duplicated error messages. This might be automated in future for CollectionType class but needs more real-life testing before doing that.

If you are sure this is not a problem, create [form extension](https://symfony.com/doc/current/form/create_form_type_extension.html) that will nullify it for all collections in your project.

---

With default mapper, you would have to use some class as DTO and inject $em into it, just so you can call ``$em->persist()`` and ``$em->remove()`` when you add or remove a product. 

``add_value`` and ``remove_value`` will still receive $data as second parameter but because we don't need mapping here, it is not shown.

---

In above example, only products are being edited. But you can populate ``$data`` with anything you want, you can mix with other entities and collections as well. You are not limited anymore to one central object.

There is just one thing left, [validation](/docs/validation.md).

