<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Model;

use EveryWorkflow\MongoBundle\Support\SeederInterface;

interface SeederListInterface
{
    /**
     * @return SeederInterface[]
     */
    public function getSortedList(): array;
}
