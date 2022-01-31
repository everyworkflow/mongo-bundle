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
use MongoDB\UpdateResult;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * BaseDocumentRepository constructor.
 */
class BaseDocumentRepository extends BaseRepository implements BaseDocumentRepositoryInterface
{
    protected array $indexKeys = [];
    protected string $eventPrefix = '';
    protected ?string $documentClass = null;
    protected ?RepositoryAttribute $repositoryAttribute = null;

    protected DocumentFactoryInterface $documentFactory;
    protected CoreHelperInterface $coreHelper;
    protected SystemDateTimeInterface $systemDateTime;
    protected ValidatorFactoryInterface $validatorFactory;
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * BaseDocumentRepository constructor.
     */
    public function __construct(
        MongoConnectionInterface $mongoConnection,
        DocumentFactoryInterface $documentFactory,
        CoreHelperInterface $coreHelper,
        SystemDateTimeInterface $systemDateTime,
        ValidatorFactoryInterface $validatorFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($mongoConnection);
        $this->documentFactory = $documentFactory;
        $this->coreHelper = $coreHelper;
        $this->systemDateTime = $systemDateTime;
        $this->validatorFactory = $validatorFactory;
        $this->eventDispatcher = $eventDispatcher;
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

    public function getEventPrefix(): string
    {
        if (empty($this->eventPrefix) && $this->getRepositoryAttribute()) {
            $this->eventPrefix = $this->getRepositoryAttribute()->getEventPrefix();
        }

        return $this->eventPrefix;
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

    public function deleteOneByFilter(array $filter = []): object|array|null
    {
        return $this->getCollection()->findOneAndDelete($filter);
    }

    public function deleteByFilter(array $filter = []): object|array|null
    {
        return $this->getCollection()->deleteMany($filter);
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
        $this->eventDispatcher->dispatch(
            $document,
            $this->getEventPrefix() . '_save_one_before'
        );
        $validData = $this->getValidDocumentData($document);
        $filter = $this->getDocumentFilter($document, $validData, $otherFilter);
        if (empty($filter)) {
            $result = $this->getCollection()->insertOne($validData);
            $validData['_id'] = $result->getInsertedId();
        } else {
            $options = array_merge(['upsert' => true], $otherOptions);
            $result = $this->getCollection()->updateOne($filter, ['$set' => $validData], $options);

            if (1 !== $result->getModifiedCount() && 1 !== $result->getUpsertedCount() && 1 !== $result->getMatchedCount()) {
                throw new \Exception('Couldn\'t save document.');
            }

            if (1 === $result->getUpsertedCount()) {
                $validData['_id'] = $result->getUpsertedId();
            }
        }

        $newDocument = $this->create($validData);
        $this->eventDispatcher->dispatch(
            $newDocument,
            $this->getEventPrefix() . '_save_one_after'
        );

        return $newDocument;
    }

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     */
    public function insertOne(ArrayableInterface $document): BaseDocumentInterface
    {
        $this->eventDispatcher->dispatch(
            $document,
            $this->getEventPrefix() . '_insert_one_before'
        );
        $validData = $this->getValidDocumentData($document);
        $result = $this->getCollection()->insertOne($validData);
        $validData['_id'] = $result->getInsertedId();

        $newDocument = $this->create($validData);
        $this->eventDispatcher->dispatch(
            $newDocument,
            $this->getEventPrefix() . '_insert_one_after'
        );

        return $newDocument;
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
        $this->eventDispatcher->dispatch(
            $document,
            $this->getEventPrefix() . '_update_one_before'
        );
        $validData = $this->getValidDocumentData($document);
        $filter = $this->getDocumentFilter($document, $validData, $otherFilter);
        $result = $this->getCollection()->updateOne($filter, ['$set' => $validData], $otherOptions);

        if (1 !== $result->getModifiedCount()) {
            throw new \Exception('Couldn\'t find document to update.');
        }

        $newDocument = $this->create($validData);
        $this->eventDispatcher->dispatch(
            $newDocument,
            $this->getEventPrefix() . '_update_one_after'
        );

        return $newDocument;
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
        $this->eventDispatcher->dispatch(
            $document,
            $this->getEventPrefix() . '_replace_one_before'
        );
        $validData = $this->getValidDocumentData($document);
        $filter = $this->getDocumentFilter($document, $validData, $otherFilter);
        $result = $this->getCollection()->replaceOne($filter, $validData, $otherOptions);

        if (1 !== $result->getModifiedCount()) {
            throw new \Exception('Couldn\'t find document to replace.');
        }

        $newDocument = $this->create($validData);
        $this->eventDispatcher->dispatch(
            $newDocument,
            $this->getEventPrefix() . '_replace_one_after'
        );

        return $newDocument;
    }

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     */
    protected function getValidDocumentData(ArrayableInterface $document, array $otherFilter = []): array
    {
        $this->eventDispatcher->dispatch(
            $document,
            $this->getEventPrefix() . '_validate_document_before'
        );

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

        $newDocument = $this->create($documentData);
        $this->eventDispatcher->dispatch(
            $newDocument,
            $this->getEventPrefix() . '_validate_document_after'
        );

        return $newDocument->toArray();
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
                    if ('_id' === $key) {
                        if (isset($documentData[$key]) && !empty($documentData[$key])) {
                            if (is_string($documentData[$key])) {
                                $keyFilters[$key] = new \MongoDB\BSON\ObjectId($documentData[$key]);
                            } else if ($documentData[$key] instanceof \MongoDB\BSON\ObjectId) {
                                $keyFilters[$key] = $documentData[$key];
                            }
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
            if ('_id' === $this->getPrimaryKey()) {
                if (isset($documentData[$this->getPrimaryKey()]) && !empty($documentData[$this->getPrimaryKey()])) {
                    if (is_string($documentData[$this->getPrimaryKey()])) {
                        $filter[$this->getPrimaryKey()] = new \MongoDB\BSON\ObjectId($documentData[$this->getPrimaryKey()]);
                    } else if ($documentData[$this->getPrimaryKey()] instanceof \MongoDB\BSON\ObjectId) {
                        $filter[$this->getPrimaryKey()] = $documentData[$this->getPrimaryKey()];
                    }
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
            $items[] = $this->create(json_decode(json_encode($mongoItem), true));
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

        return $this->create(json_decode(json_encode($mongoItem), true));
    }

    /**
     * @throws \Exception
     */
    public function findById(string | \MongoDB\BSON\ObjectId $uuid): BaseDocumentInterface
    {
        if ($uuid instanceof \MongoDB\BSON\ObjectId) {
            $filter = ['_id' => $uuid];
        } else {
            try {
                $filter = ['_id' => new \MongoDB\BSON\ObjectId($uuid)];
            } catch (\Exception $e) {
                throw new \Exception('Invalid ID: ' . $uuid);
            }
        }

        return $this->findOne($filter);
    }

    public function bulkUpdateByIds(
        array $ids = [],
        array $updateData = [],
        array $options = []
    ): UpdateResult {
        $updateIds = [];
        foreach ($ids as $id) {
            if ($id instanceof \MongoDB\BSON\ObjectId) {
                $updateIds[] = $id;
            } else if (is_string($id)) {
                $updateIds[] = new \MongoDB\BSON\ObjectId($id);
            }
        }
        $filter = ['_id' => ['$in' => $updateIds]];

        $result = $this->getCollection()->updateMany($filter, [
            '$set' => $updateData,
        ], $options);

        return $result;
    }
}
