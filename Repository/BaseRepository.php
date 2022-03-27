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
     * @var string $collectionName - Name of mongodb repository collection.
     * @var string|array $primaryKey - Primary keys must be defined.
     */
    public function __construct(
        protected MongoConnectionInterface $mongoConnection,
        protected string $collectionName = '',
        protected string|array $primaryKey = ''
    ) {
    }

    public function getConnection(): MongoConnectionInterface
    {
        return $this->mongoConnection;
    }

    public function setConnection(MongoConnectionInterface $mongoConnection): self
    {
        $this->mongoConnection = $mongoConnection;

        return $this;
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
