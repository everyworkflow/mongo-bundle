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
     * Primary keys must be defined.
     */
    protected string|array $primaryKey = '';

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
        return $this->getConnection()
            ->getDatabase()
            ->selectCollection($this->getCollectionName());
    }

    public function getPrimaryKey(): string|array
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey(string|array $primaryKey): self
    {
        $this->primaryKey = $primaryKey;
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
