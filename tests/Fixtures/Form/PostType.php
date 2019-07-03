<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Tests\Fixtures\Form;

use HTC\StrictFormMapper\Tests\Fixtures\Model\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('first', TextType::class, [
            'get_value' => function (Post $post) {
                return $post->getSubject();
            },
            'update_value' => function (string $subject, Post $post) {
                $post->setSubject($subject);
            },
            'write_error_message' => 'Subject cannot be empty.',
        ]);

        $builder->add('second', TextType::class, [
            'get_value' => function (Post $post) {
                return $post->getBody();
            },
            'update_value' => function (string $body, Post $post) {
                $post->setBody($body);
            },
            'write_error_message' => 'Body cannot be empty.',
        ]);

        $builder->add('third', CollectionType::class, [
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => TextType::class,
            'entry_options' => [
                'factory' => function (FormInterface $form) {
                    dd($form);
                },
            ],
            'get_value' => function (Post $post) {
                return $post->getTags();
            },
            'add_value' => function (string $tag, Post $post) {
                $post->addTag($tag);
            },
            'remove_value' => function (string $tag, Post $post) {
                $post->removeTag($tag);
            },
            // no need for errors when reading and writing, child class will show its own error
            'write_error_message' => null,
        ]);
    }

    public function factory(string $first, string $second): Post
    {
        return new Post($first, $second);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'factory' => [$this, 'factory'],
            'factory_error_message' => 'Cannot create post entity.',
        ]);
    }
}
