<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Model;

use MongoDB\Client;
use MongoDB\Database;

interface MongoConnectionInterface
{
    public function getClient(): Client;

    public function getDatabase(): Database;
}
