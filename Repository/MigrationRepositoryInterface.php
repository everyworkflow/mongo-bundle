<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\MongoBundle\Document\MigrationDocumentInterface;

interface MigrationRepositoryInterface extends BaseRepositoryInterface
{
    public function mapMigration(array $data): MigrationDocumentInterface;

    /**
     * @return MigrationDocumentInterface[]
     */
    public function find(array $filters = [], array $options = []): array;

    /**
     * @param MigrationDocumentInterface[]
     * @return \MongoDB\InsertManyResult
     */
    public function insertMany(array $migrations): \MongoDB\InsertManyResult;
}
