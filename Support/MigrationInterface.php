<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Support;

interface MigrationInterface
{
    public const SUCCESS = true;
    public const FAILURE = false;

    /**
     * This function will execute while migration.
     */
    public function migrate(): bool;

    /**
     * And this function will execute while rollback.
     */
    public function rollback(): bool;
}
