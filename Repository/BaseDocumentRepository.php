<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\CoreBundle\Exception\ValidatorException;
use EveryWorkflow\CoreBundle\Factory\ValidatorFactoryInterface;
use EveryWorkflow\CoreBundle\Helper\CoreHelperInterface;
use EveryWorkflow\CoreBundle\Model\SystemDateTimeInterface;
use EveryWorkflow\CoreBundle\Support\ArrayableInterface;
use EveryWorkflow\MongoBundle\Document\BaseDocumentInterface;
use EveryWorkflow\MongoBundle\Document\HelperTrait\CreatedUpdatedHelperTraitInterface;
use EveryWorkflow\MongoBundle\Exception\PrimaryKeyMissingException;
use EveryWorkflow\MongoBundle\Factory\DocumentFactoryInterface;
use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;
use EveryWorkflow\MongoBundle\Support\Attribute\RepositoryAttribute;
use MongoDB\Model\BSONDocument;
use ReflectionClass;

/**
 * BaseDocumentRepository constructor.
 */
class BaseDocumentRepository extends BaseRepository implements BaseDocumentRepositoryInterface
{
    protected array $indexKeys = [];
    protected ?string $documentClass = null;
    protected ?RepositoryAttribute $repositoryAttribute = null;

    protected DocumentFactoryInterface $documentFactory;
    protected CoreHelperInterface $coreHelper;
    protected SystemDateTimeInterface $systemDateTime;
    protected ValidatorFactoryInterface $validatorFactory;

    /**
     * BaseDocumentRepository constructor.
     */
    public function __construct(
        MongoConnectionInterface $mongoConnection,
        DocumentFactoryInterface $documentFactory,
        CoreHelperInterface $coreHelper,
        SystemDateTimeInterface $systemDateTime,
        ValidatorFactoryInterface $validatorFactory
    ) {
        parent::__construct($mongoConnection);
        $this->documentFactory = $documentFactory;
        $this->coreHelper = $coreHelper;
        $this->systemDateTime = $systemDateTime;
        $this->validatorFactory = $validatorFactory;
    }

    public function getRepositoryAttribute(): ?RepositoryAttribute
    {
        if (!$this->repositoryAttribute) {
            $reflectionClass = new ReflectionClass(get_class($this));
            $attributes = $reflectionClass->getAttributes(RepositoryAttribute::class);
            foreach ($attributes as $attribute) {
                $this->repositoryAttribute = $attribute->newInstance();
            }
        }

        return $this->repositoryAttribute;
    }

    public function getPrimaryKey(): string|array
    {
        if (empty($this->primaryKey) && $this->getRepositoryAttribute()) {
            $this->primaryKey = $this->getRepositoryAttribute()->getPrimaryKey();
        }

        return $this->primaryKey;
    }

    public function getIndexKeys(): array
    {
        if (empty($this->indexKeys) && $this->getRepositoryAttribute()) {
            $indexKeys = $this->getPrimaryKey();
            if (is_string($indexKeys)) {
                $indexKeys = [$indexKeys];
            }
            $attrIndexKey = $this->getRepositoryAttribute()->getIndexKey();
            if (is_string($attrIndexKey)) {
                $indexKeys = [...$indexKeys, $attrIndexKey];
            } else if (is_array($attrIndexKey)) {
                $indexKeys = [...$indexKeys, ...$attrIndexKey];
            }
            $this->indexKeys = $indexKeys;
        }

        return $this->indexKeys;
    }

    public function getCollectionName(): string
    {
        if (empty($this->collectionName) && $this->getRepositoryAttribute()) {
            $this->collectionName = $this->getRepositoryAttribute()->getCollectionName();
        }

        return $this->collectionName;
    }

    public function setDocumentClass(string $documentClass): self
    {
        $this->documentClass = $documentClass;

        return $this;
    }

    public function getDocumentClass(): ?string
    {
        if (!$this->documentClass && $this->getRepositoryAttribute()) {
            $this->documentClass = $this->getRepositoryAttribute()->getDocumentClass();
        }

        return $this->documentClass;
    }

    public function create(array $data = []): BaseDocumentInterface
    {
        return $this->documentFactory->create($this->getDocumentClass(), $data);
    }

    public function deleteByFilter(array $filter = []): object|array|null
    {
        return $this->getCollection()->findOneAndDelete($filter);
    }

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function saveOne(
        ArrayableInterface $document,
        array $otherFilter = [],
        array $otherOptions = []
    ): BaseDocumentInterface {
        $validData = $this->getValidDocumentData($document);
        $filter = $this->getDocumentFilter($document, $validData, $otherFilter);
        if (empty($filter)) {
            return $this->insertOne($document, $otherOptions);
        }
        
        $options = array_merge(['upsert' => true], $otherOptions);
        $result = $this->getCollection()->updateOne($filter, ['$set' => $validData], $options);

        if (1 !== $result->getModifiedCount() && 1 !== $result->getUpsertedCount() && 1 !== $result->getMatchedCount()) {
            throw new \Exception('Couldn\'t save document.');
        }

        if (1 === $result->getUpsertedCount()) {
            $validData['_id'] = $result->getUpsertedId();
        }

        return $this->create($validData);
    }

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     */
    public function insertOne(ArrayableInterface $document): BaseDocumentInterface
    {
        $validData = $this->getValidDocumentData($document);
        $result = $this->getCollection()->insertOne($validData);
        $validData['_id'] = $result->getInsertedId();

        return $this->create($validData);
    }

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function updateOne(
        ArrayableInterface $document,
        array $otherFilter = [],
        array $otherOptions = []
    ): BaseDocumentInterface {
        $validData = $this->getValidDocumentData($document);
        $filter = $this->getDocumentFilter($document, $validData, $otherFilter);
        $result = $this->getCollection()->updateOne($filter, ['$set' => $validData], $otherOptions);

        if (1 !== $result->getModifiedCount()) {
            throw new \Exception('Couldn\'t find document to update.');
        }

        return $this->create($validData);
    }

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function replaceOne(
        ArrayableInterface $document,
        array $otherFilter = [],
        array $otherOptions = []
    ): BaseDocumentInterface {
        $validData = $this->getValidDocumentData($document);
        $filter = $this->getDocumentFilter($document, $validData, $otherFilter);
        $result = $this->getCollection()->replaceOne($filter, $validData, $otherOptions);

        if (1 !== $result->getModifiedCount()) {
            throw new \Exception('Couldn\'t find document to replace.');
        }

        return $this->create($validData);
    }

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     */
    protected function getValidDocumentData(ArrayableInterface $document, array $otherFilter = []): array
    {
        $documentData = $document->toArray();
        $this->validatePrimaryKey($documentData, $otherFilter);

        if ($document instanceof CreatedUpdatedHelperTraitInterface) {
            if ($document->getCreatedAt() === null) {
                $document->setCreatedAt($this->systemDateTime->utcNow());
            }
            $document->setUpdatedAt($this->systemDateTime->utcNow());
        }

        $validator = $this->validatorFactory->create();
        if (!$validator->validate($document)) {
            throw (new ValidatorException('Document data is not valid.'))->setValidator($validator);
        }
        $documentData = $validator->getValidData();

        if (isset($documentData['_id'])) {
            unset($documentData['_id']);
        }

        return $documentData;
    }

    /**
     * @throws PrimaryKeyMissingException
     */
    protected function validatePrimaryKey(array $documentData, array $otherFilter = []): void
    {
        $data = array_merge($documentData, $otherFilter);
        if (is_array($this->getPrimaryKey())) {
            foreach ($this->getPrimaryKey() as $key) {
                if ('_id' !== $key && !isset($data[$key])) {
                    throw new PrimaryKeyMissingException('Primary Key (' . $key . ') missing.');
                }
            }
        } elseif ('_id' !== $this->getPrimaryKey() && !isset($data[$this->getPrimaryKey()])) {
            throw new PrimaryKeyMissingException('Primary Key (' . $this->getPrimaryKey() . ') missing.');
        }
    }

    protected function getDocumentFilter(ArrayableInterface $document, array $validData, array $otherFilter = []): array
    {
        $documentData = $document->toArray();
        $filter = $otherFilter;

        if (is_array($this->getPrimaryKey())) {
            $keyFilters = [];
            foreach ($this->getPrimaryKey() as $key) {
                if (!isset($filter[$key])) {
                    if ('_id' === $key && isset($documentData[$key])) {
                        if (is_string($documentData[$key])) {
                            $keyFilters[$key] = new \MongoDB\BSON\ObjectId($documentData[$key]);
                        } else if ($documentData[$key] instanceof \MongoDB\BSON\ObjectId) {
                            $keyFilters[$key] = $documentData[$key];
                        }
                    } else if (isset($validData[$key])) {
                        $keyFilters[$key] = $validData[$key];
                    }
                }
            }
            if (!empty($keyFilters)) {
                $filter = array_merge($filter, $keyFilters);
            }
        } elseif (!isset($filter[$this->getPrimaryKey()])) {
            if ('_id' === $this->getPrimaryKey() && isset($documentData[$this->getPrimaryKey()])) {
                if (is_string($documentData[$this->getPrimaryKey()])) {
                    $filter[$this->getPrimaryKey()] = new \MongoDB\BSON\ObjectId($documentData[$this->getPrimaryKey()]);
                } else if ($documentData[$this->getPrimaryKey()] instanceof \MongoDB\BSON\ObjectId) {
                    $filter[$this->getPrimaryKey()] = $documentData[$this->getPrimaryKey()];
                }
            } else if (isset($validData[$this->getPrimaryKey()])) {
                $filter[$this->getPrimaryKey()] = $validData[$this->getPrimaryKey()];
            }
        }

        return $filter;
    }

    /**
     * @return BaseDocumentInterface[]
     */
    public function find(array $filter = [], array $options = []): array
    {
        $items = [];
        $mongoData = $this->getCollection()->find($filter, $options);
        /** @var BSONDocument $mongoItem */
        foreach ($mongoData as $mongoItem) {
            $items[] = $this->create($mongoItem->getArrayCopy());
        }

        return $items;
    }

    /**
     * @throws \Exception
     */
    public function findOne(array $filter = [], array $options = []): BaseDocumentInterface
    {
        $mongoItem = $this->getCollection()->findOne($filter, $options);
        if (!$mongoItem) {
            throw new \Exception('Document not found under ' . $this->getCollectionName());
        }

        return $this->create($mongoItem->getArrayCopy());
    }

    /**
     * @throws \Exception
     */
    public function findById(string | \MongoDB\BSON\ObjectId $uuid): BaseDocumentInterface
    {
        if ($uuid instanceof \MongoDB\BSON\ObjectId) {
            $filter = ['_id' => $uuid];
        } else {
            $filter = ['_id' => new \MongoDB\BSON\ObjectId($uuid)];
        }

        return $this->findOne($filter);
    }
}
