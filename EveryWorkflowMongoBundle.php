<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle;

use EveryWorkflow\MongoBundle\DependencyInjection\MongoExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EveryWorkflowMongoBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new MongoExtension();
    }
}
