When you add field to form, Symfony will use ``get*`` and ``set*`` prefix to access it:



```php
$builder->add('subject', TextType::class);

```

This forces you to write following methods:

```php
// App\Entity\Blog.php
class Blog
{
    private ?string $subject;
    
    public function getSubject(): ?string
    {
        return $this->subject;
    }
    
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }
}
```

This brings few problems; 
- you are forced to ``get*/set*`` naming convention in your classes
- even though your logic requires string value, not using nullable will throw TypeError exception
- renaming methods will break your form
- these methods will be reported as ``unused``



Instead, you can use this:

```php
$builder->add('subject', TextType::class, [
    'get_value' => fn(Blog $blog) => $blog->getSubject(),
    'update_value' => fn (string $subject, Blog $blog) => $blog->updateSubject($subject),
    'write_error_message' => 'You cannot leave this field empty.',
]);
```

Your entity can now be:

```php
// App\Entity\Blog.php
class Blog
{
    // this is strict now
    private string $subject;
    
    public function getSubject(): string 
    {
        returtn $this->subject;
    }
    
    public function updateSubject(string $subject): void 
    {
        $this->subject = $subject;
    }
}
```

The value of ``write_error_message`` will be rendered as field error when TypeError exception is thrown. If you leave it empty, you must provide ``NotNull`` constraint that will deal with it.

---

If the field is a collection, you can use adders and removers, instead of setter:


```php
$builder->add('tags', EntityType::class, [
    'class' => Tag::class,
    'multiple' => true,
    'get_value' => fn(Blog $blog) => $blog->getTags(),
    'add_value' => fn(Tag $tag, Blog $blog) => $blog->addTag($tag),
    'remove_value' => fn(Tag $tag, Blog $blog) => $blog->removeTag($tag),
    'write_error_message' => null, // child forms will display errors, we don't need it here
);
```

This is very similar to default behavior except that it allows you to safely rename your methods or use different field name, without resorting to ``property_path`` option.
