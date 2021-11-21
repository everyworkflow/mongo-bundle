<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\DependencyInjection;

use EveryWorkflow\MongoBundle\Support\MigrationInterface;
use EveryWorkflow\MongoBundle\Support\SeederInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MongoExtension extends Extension
{
    /**
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container->registerForAutoconfiguration(MigrationInterface::class)
            ->addTag('everyworkflow.migration');

        $container->registerForAutoconfiguration(SeederInterface::class)
        ->addTag('everyworkflow.seeder');
    }
}
