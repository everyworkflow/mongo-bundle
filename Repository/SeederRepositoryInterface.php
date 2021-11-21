<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\MongoBundle\Document\SeederDocumentInterface;

interface SeederRepositoryInterface extends BaseRepositoryInterface
{
    public function mapDocument(array $data): SeederDocumentInterface;

    /**
     * @return SeederDocumentInterface[]
     */
    public function find(array $filters = [], array $options = []): array;

    /**
     * @param SeederDocumentInterface[]
     * @return \MongoDB\InsertManyResult
     */
    public function insertMany(array $items): \MongoDB\InsertManyResult;
}
