<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;
use MongoDB\Collection;

class BaseRepository implements BaseRepositoryInterface
{
    /**
     * Collection name must be defined.
     */
    protected string $collectionName = '';

    /**
     * index name must be defined.
     */
    protected array $indexNames = ['_id'];

    protected MongoConnectionInterface $mongoConnection;

    public function __construct(MongoConnectionInterface $mongoConnection)
    {
        $this->mongoConnection = $mongoConnection;
    }

    public function getConnection(): MongoConnectionInterface
    {
        return $this->mongoConnection;
    }

    public function getCollection(): Collection
    {
        return $this->getConnection()->getDatabase()->selectCollection($this->collectionName);
    }

    public function getIndexNames(): array
    {
        return $this->indexNames;
    }

    public function setIndexNames(array $indexNames): self
    {
        $this->indexNames = $indexNames;
        return $this;
    }

    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    public function setCollectionName(string $collectionName): self
    {
        $this->collectionName = $collectionName;
        return $this;
    }
}
