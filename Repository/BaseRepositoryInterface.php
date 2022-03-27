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

    public function setConnection(MongoConnectionInterface $mongoConnection): self;

    public function getCollection(): Collection;

    public function getPrimaryKey(): string|array;

    public function setPrimaryKey(string|array $primaryKeys): self;

    public function getCollectionName(): string;

    public function setCollectionName(string $collectionName): self;
}
