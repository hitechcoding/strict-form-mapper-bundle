<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Form\Extension;

use HTC\StrictFormMapper\Form\DataMapper\StrictFormMapper;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TypeError;
use Closure;
use function is_array;
use function array_map;

class StrictFormTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['compound']) {
            $originalMapper = $builder->getDataMapper();
            if (!$originalMapper) {
                throw new \InvalidArgumentException('Mapper not found');
            }
            $builder->setDataMapper(new StrictFormMapper($originalMapper));
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'get_value' => null,
            'update_value' => null,
            'write_error_message' => 'Cannot write this type',
            'factory' => null,
            'factory_error_message' => 'Some fields are not valid, please correct them.',
        ]);

        $resolver->setAllowedTypes('get_value', ['null', 'callable']);
        $resolver->setAllowedTypes('update_value', ['null', 'callable']);
        $resolver->setAllowedTypes('write_error_message', ['null', 'string']);
        $resolver->setAllowedTypes('factory', ['null', 'callable']);
        $resolver->setAllowedTypes('factory_error_message', ['null', 'string']);

        $resolver->setNormalizer('empty_data', function (Options $options, $value) {
            /** @var null|callable $factory */
            $factory = $options['factory'];
            if (!$factory) {
                return $value;
            }



            return function (FormInterface $form) use ($factory, $options) {
                $arguments = $this->getSubmittedValuesFromFactorySignature($factory, $form);
                try {
                    return $factory(...$arguments);
                } catch (TypeError $e) {
                    /** @var null|string $errorMessage */
                    $errorMessage = $options['factory_error_message'];
                    if ($errorMessage) {
                        $form->addError(new FormError($errorMessage, null, [], null, $e));
                    }

                    return null;
                }
            };
        });
    }

    private function getSubmittedValuesFromFactorySignature(callable $factory, FormInterface $form): array
    {
        $parameterNames = $this->getParameterNamesFromCallable($factory);

        return array_map(function (string $name) use ($form) {
            return $form->get($name)->getData();
        }, $parameterNames);
    }

    private function getParameterNamesFromCallable(callable $factory): array
    {
        if (is_array($factory)) {
            $rf = new ReflectionMethod($factory[0], $factory[1]);
        } elseif ($factory instanceof Closure) {
            $rf = new ReflectionFunction($factory);
        } else {
            throw new InvalidArgumentException('Unsupported callable, use Closures or [$object, "method"] syntax.');
        }

        return array_map(function (ReflectionParameter $reflectionParameter) {
            return $reflectionParameter->getName();
        }, $rf->getParameters());
    }
}
