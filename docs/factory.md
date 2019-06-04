Consider the following code:

```php
/**
* @Route("/create")
*/
public function createBlog(Request $request): Response
{
    $form = $this->createForm(BlogType::class);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $blog = $form->getData();
        // persist and flush

        return $this->redirectToRoute('...');
    }

    return $this->render('blog/form.html.twig', [
        'form' => $form->createView(),
    ]);
}
```

When there is no underlying data, Symfony will look for [``empty_data``](hhttps://symfony.com/doc/current/form/use_empty_data.html) callable in attempt to create it. If we assume that blog must always have a subject, you would add this to your form:

```php
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'empty_data' => fn (FormInterface $form) => new Blog($form->get('subject')->getData()),
    ]);
}
```

and your entity:

```php
class Blog
{
    private string $subject;
    
    public function __construct(string $subject)
    {
        $this->subject = $subject;
    }
}
```

The problem is that ``empty_data`` is not really readable and in case that ``subject`` is not a string, it will throw an exception.

Let's try this:

```php
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'factory' => fn (string $subject) => new Blog($subject)),
        'factory_error_message' => 'Cannot create new instance of Blog entity',
    ]);
}
```

### Warning:
The parameters of ``factory`` callable must be named as fields of your form, order is irrelevant. 

---

You can also extract factory to method using ``[$object, $method]`` syntax:

```php
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'factory' => [$this, 'factory'],
    ]);
}

public function factory(string $subject): Blog
{
    return new Blog($subject);
}
```

Same rule applies; name of parameters must match names of form fields. It is the only way bundle can know what value you want.

If TypeError exception is thrown, it will be converted to validation error with message from ``factory_error_message``. In most cases you would want to suppress it (``'factory_error_message' => null``) because error on ``subject`` field should be enough.