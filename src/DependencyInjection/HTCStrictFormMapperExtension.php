<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\DependencyInjection;

use HTC\StrictFormMapper\Contract\ValueVoterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class HTCStrictFormMapperExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->registerForAutoconfiguration(ValueVoterInterface::class)->addTag('htc_strict_form_mapper.voter');
    }
}
