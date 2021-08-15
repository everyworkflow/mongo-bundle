<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

namespace EveryWorkflow\MongoBundle\Tests;

use EveryWorkflow\CoreBundle\Tests\BaseTestCase;
use EveryWorkflow\MongoBundle\Model\MongoConnection;
use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;

class BaseMongoTestCase extends BaseTestCase
{
    public function getMongoConnection(?string $mongoUri = null, ?string $mongoDb = null): MongoConnectionInterface
    {
        if ($mongoUri === null) {
            $mongoUri = $_ENV['MONGO_URI'];
        }
        if ($mongoDb === null) {
            $mongoDb = $_ENV['MONGO_DB'];
        }
        return new MongoConnection($mongoUri, $mongoDb);
    }
}
