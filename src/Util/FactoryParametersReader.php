<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Util;

use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Closure;
use function is_array;

class FactoryParametersReader
{
    public static function getCallableArguments(callable $factory, FormInterface $form): array
    {
        $reflection = self::getReflection($factory);
        $arguments = [];
        foreach ($reflection->getParameters() as $parameter) {
            $parameter->getClass();
            $type = $parameter->getClass();

            if ($type && $type->implementsInterface(FormInterface::class)) {
                $arguments[] = $form;
            } else {
                $arguments[] = $form->get($parameter->getName())->getData();
            }
        }

        return $arguments;
    }

    private static function getReflection($factory): ReflectionFunctionAbstract
    {
        if (is_array($factory)) {
            $rf = new ReflectionMethod($factory[0], $factory[1]);
        } elseif ($factory instanceof Closure) {
            $rf = new ReflectionFunction($factory);
        } else {
            throw new InvalidArgumentException('Unsupported callable, use Closures or [$object, "method"] syntax.');
        }

        return $rf;
    }
}
