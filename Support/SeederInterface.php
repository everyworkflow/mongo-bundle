<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Support;

interface SeederInterface
{
    public const SUCCESS = true;
    public const FAILURE = false;

    /**
     * This function will execute while seed.
     */
    public function seed(): bool;

    /**
     * And this function will execute while rollback.
     */
    public function rollback(): bool;
}
