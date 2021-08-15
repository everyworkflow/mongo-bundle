<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;
use MongoDB\Collection;

interface BaseRepositoryInterface
{
    public function getConnection(): MongoConnectionInterface;

    public function getCollection(): Collection;

    public function getIndexNames(): array;

    public function setIndexNames(array $indexNames): self;

    public function getCollectionName(): string;

    public function setCollectionName(string $collectionName): self;
}
