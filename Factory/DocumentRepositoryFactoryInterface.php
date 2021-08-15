<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Factory;

use EveryWorkflow\MongoBundle\Repository\BaseDocumentRepositoryInterface;

interface DocumentRepositoryFactoryInterface
{
    public function create(): BaseDocumentRepositoryInterface;
}
