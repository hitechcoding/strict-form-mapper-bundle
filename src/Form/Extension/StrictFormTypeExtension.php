<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Form\Extension;

use HTC\StrictFormMapper\Form\DataMapper\StrictFormMapper;
use HTC\StrictFormMapper\Util\FactoryParametersReader;
use InvalidArgumentException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Exception\OutOfBoundsException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use TypeError;

class StrictFormTypeExtension extends AbstractTypeExtension
{
    /** @var iterable */
    private $voters;

    private $translator;

    public function __construct($voters, ?TranslatorInterface $translator)
    {
        $this->voters = $voters;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['compound']) {
            $originalMapper = $builder->getDataMapper();
            if (!$originalMapper) {
                throw new InvalidArgumentException('Mapper not found');
            }

            $builder->setDataMapper(new StrictFormMapper($originalMapper, $this->voters, $this->translator));
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function getExtendedType(): string
    {
        return FormType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'get_value' => null,
            'update_value' => null,
            'add_value' => null,
            'remove_value' => null,
            'write_error_message' => 'Cannot write this type',
            'factory' => null,
            'factory_error_message' => 'Some fields are not valid, please correct them.',
        ]);

        $resolver->setAllowedTypes('get_value', ['null', 'callable']);
        $resolver->setAllowedTypes('update_value', ['null', 'callable']);
        $resolver->setAllowedTypes('add_value', ['null', 'callable']);
        $resolver->setAllowedTypes('remove_value', ['null', 'callable']);
        $resolver->setAllowedTypes('write_error_message', ['null', 'string']);
        $resolver->setAllowedTypes('factory', ['null', 'callable']);
        $resolver->setAllowedTypes('factory_error_message', ['null', 'string']);

        $resolver->setNormalizer('get_value', function (Options $options, ?callable $getter) {
            if ($options['add_value'] && !$options['remove_value']) {
                throw new InvalidOptionsException('You cannot use "add_value" without "remove_value".');
            }
            if ($options['remove_value'] && !$options['add_value']) {
                throw new InvalidOptionsException('You cannot use "remove_value" without "add_value".');
            }
            if ($options['update_value'] && $options['add_value']) {
                throw new InvalidOptionsException('You cannot use "update_value" when adder and remover is set.');
            }

            $isUpdaterSet = $options['update_value'] || $options['add_value'];
            if (!$getter && $isUpdaterSet) {
                throw new InvalidOptionsException('You must define "get_value".');
            }
            if ($getter && !$isUpdaterSet) {
                throw new InvalidOptionsException('You cannot use "get_value" without "update_value" or using "add_value" and "remove_value".');
            }

            return $getter;
        });

        $resolver->setNormalizer('empty_data', function (Options $options, $value) {
            /** @var callable|null $factory */
            $factory = $options['factory'];
            if (!$factory) {
                return $value;
            }
            $errorMessage = $options['factory_error_message'];

            return function (FormInterface $form) use ($factory, $errorMessage) {
                return $this->getFactoryData($form, $factory, $errorMessage);
            };
        });
    }

    private function getFactoryData(FormInterface $form, callable $factory, ?string $errorMessage)
    {
        try {
            $arguments = FactoryParametersReader::getCallableArguments($factory, $form);

            return $factory(...$arguments);
        } catch (OutOfBoundsException $e) {
            throw new OutOfBoundsException($e->getMessage().' Make sure your factory signature matches form fields.');
        } catch (TypeError $e) {
            if ($errorMessage) {
                $translatedMessage = $this->translator ? $this->translator->trans($errorMessage) : $errorMessage;
                $form->addError(new FormError($translatedMessage, null, [], null, $e));
            }

            return null;
        }
    }
}
