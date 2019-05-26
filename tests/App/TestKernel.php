<?php

declare(strict_types=1);

namespace HTC\SpaBundle\Tests\App;

use HTC\StrictFormMapper\HTCStrictFormMapperBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle(),
            new HTCStrictFormMapperBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/test_services.yaml');

        $c->loadFromExtension('framework', [
            'secret' => 'htc_spa_secret',
            'test' => true,
        ]);
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
    }
}
