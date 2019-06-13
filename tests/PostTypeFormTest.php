<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Tests;

use HTC\StrictFormMapper\Tests\Fixtures\Form\PostType;
use HTC\StrictFormMapper\Tests\Fixtures\Model\Post;
use function array_values;
use HTC\StrictFormMapper\Tests\Model\AbstractTypeTest;

class PostTypeFormTest extends AbstractTypeTest
{
    public function testFactoryFailureWillMakeValidationError(): void
    {
        $form = $this->factory->create(PostType::class);
        $form->submit($this->getInvalidData());

        $subject = $form->get('first');
        $body = $form->get('second');

        $this->assertFalse($form->isValid());
        $this->assertFalse($subject->isValid());
        $this->assertTrue($body->isValid());
        // this is just for failed factory
        $this->assertCount(1, $form->getErrors(false));
    }

    public function testNoFactoryErrorMessage(): void
    {
        $form = $this->factory->create(PostType::class, null, [
            'factory_error_message' => null,
        ]);
        $form->submit($this->getInvalidData());

        $subject = $form->get('first');
        $body = $form->get('second');

        $this->assertFalse($form->isValid());
        $this->assertFalse($subject->isValid());
        $this->assertTrue($body->isValid());
        // this is just for failed factory
        $this->assertCount(0, $form->getErrors(false));
    }

    public function testAdderAndRemover(): void
    {
        $form = $this->factory->create(PostType::class);
        $form->submit($this->getValidData());

        /** @var Post $post */
        $post = $form->getData();
        // we are not interested in keys
        $tags = array_values($post->getTags());
        $this->assertEquals(['foo', 'hello world'], $tags);
    }

    private function getValidData(): array
    {
        return [
            'first' => 'Subject of post',
            'second' => 'Body of post',
            'third' => [
                'foo',
                'hello world',
            ],
        ];
    }

    private function getInvalidData(): array
    {
        return [
            'first' => null,
            'second' => 'Body of post',
        ];
    }
}
