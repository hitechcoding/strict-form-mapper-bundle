<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
    }
}
