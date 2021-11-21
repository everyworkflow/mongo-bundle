<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Model;

use EveryWorkflow\MongoBundle\Support\MigrationInterface;

interface MigrationListInterface
{
    /**
     * @return MigrationInterface[]
     */
    public function getSortedList(): array;
}
