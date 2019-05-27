<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Tests;

use HTC\StrictFormMapper\Form\Extension\StrictFormTypeExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class ExtensionConfigurationTest extends TestCase
{
    /** @var FormFactoryInterface */
    private $formFactory;

    public function setUp()
    {
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new StrictFormTypeExtension())
            ->getFormFactory();
    }

    public function testGetterWithoutUpdater(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->createEmptyForm([
            'get_value' => function () {},
            'update_value' => null,
        ]);
    }

    public function testUpdaterWithoutGetter(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->createEmptyForm([
            'get_value' => null,
            'update_value' => function () {},
        ]);
    }

    public function testAdderWithoutRemover(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->createEmptyForm([
            'get_value' => function () {},
            'add_value' => function () {},
            'remove_value' => null,
        ]);
    }

    public function testRemoverWithoutAdder(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->createEmptyForm([
            'get_value' => function () {},
            'add_value' => null,
            'remove_value' => function () {},
        ]);
    }

    public function testWriterWithAdderAndRemoved(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->createEmptyForm([
            'get_value' => function () {},
            'update_value' => function () {},
            'add_value' => function () {},
            'remove_value' => function () {},
        ]);
    }

    private function createEmptyForm(array $options): FormInterface
    {
        return $this->formFactory->create(FormType::class, null, $options);
    }
}
