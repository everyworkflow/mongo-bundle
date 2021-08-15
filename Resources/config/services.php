<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EveryWorkflow\MongoBundle\Command\MongoDatabaseDropCommand;
use EveryWorkflow\MongoBundle\Model\MigrationList;
use EveryWorkflow\MongoBundle\Model\MongoConnection;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('EveryWorkflow\\MongoBundle\\', '../../*')
        ->exclude('../../{DependencyInjection,Resources,Tests,Document/HelperTrait}');

    $services->set(MongoConnection::class)
        ->arg('$mongoUri', '%env(MONGO_URI)%')
        ->arg('$mongoDb', '%env(MONGO_DB)%');

    $services->set(MigrationList::class)
        ->arg('$migrations', tagged_iterator('everyworkflow.migration'));

    $services->set(MongoDatabaseDropCommand::class)
        ->arg('$mongoDb', '%env(MONGO_DB)%');
};
