<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

namespace EveryWorkflow\MongoBundle\Support\Attribute;

use Attribute;
use Doctrine\Inflector\InflectorFactory;

#[Attribute(Attribute::TARGET_CLASS)]
class RepositoryAttribute
{
    protected string $documentClass;
    protected string|array $primaryKeys;
    protected ?string $collectionName;
    protected string|array|null $indexKeys = [];
    protected ?string $eventPrefix;

    public function __construct(
        string $documentClass,
        string|array $primaryKey = '_id',
        ?string $collectionName = null,
        string|array|null $indexKey = null,
        $eventPrefix = null
    ) {
        $this->documentClass = $documentClass;
        $this->primaryKey = $primaryKey;
        $this->collectionName = $collectionName;
        $this->indexKey = $indexKey;
        $this->eventPrefix = $eventPrefix;
    }

    public function setDocumentClass(string $documentClass): self
    {
        $this->documentClass = $documentClass;

        return $this;
    }

    public function getDocumentClass(): string
    {
        return $this->documentClass;
    }

    public function setPrimaryKey(string|array $primaryKey): self
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    public function getPrimaryKey(): string|array
    {
        return $this->primaryKey;
    }

    public function setCollectionName(string $collectionName): self
    {
        $this->collectionName = $collectionName;

        return $this;
    }

    public function getCollectionName(): string
    {
        /* Build collection name using document class if doesn't exist */
        if (!$this->collectionName) {
            $docClass = $this->getDocumentClass();
            $docClassArr = explode('\\', $docClass);
            $docClassName = end($docClassArr);
            $collectionName = InflectorFactory::create()->build()->tableize($docClassName);
            $collectionClassArr = explode('_', $collectionName);
            if ($collectionClassArr[count($collectionClassArr) - 1] === 'document') {
                array_pop($collectionClassArr);
            }
            $collectionClassArr[] = 'collection';
            $collectionName = implode('_', $collectionClassArr);
            $this->collectionName = $collectionName;
        }

        return $this->collectionName;
    }

    public function setIndexKey(string|array $indexKey): self
    {
        $this->indexKey = $indexKey;

        return $this;
    }

    public function getIndexKey(): string|array|null
    {
        return $this->indexKey;
    }

    public function setEventPrefix(string $eventPrefix): self
    {
        $this->eventPrefix = $eventPrefix;

        return $this;
    }

    public function getEventPrefix(): string
    {
        return $this->eventPrefix;
    }
}
