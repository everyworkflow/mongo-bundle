<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use Carbon\Carbon;
use EveryWorkflow\CoreBundle\Helper\CoreHelperInterface;
use EveryWorkflow\CoreBundle\Support\ArrayableInterface;
use EveryWorkflow\MongoBundle\Document\BaseDocumentInterface;
use EveryWorkflow\MongoBundle\Factory\DocumentFactoryInterface;
use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;
use MongoDB\InsertOneResult;
use MongoDB\Model\BSONDocument;
use MongoDB\UpdateResult;
use ReflectionException;

/**
 * BaseDocumentRepository constructor.
 */
class BaseDocumentRepository extends BaseRepository implements BaseDocumentRepositoryInterface
{
    protected DocumentFactoryInterface $documentFactory;
    protected CoreHelperInterface $coreHelper;

    /**
     * BaseDocumentRepository constructor.
     */
    public function __construct(
        MongoConnectionInterface $mongoConnection,
        DocumentFactoryInterface $documentFactory,
        CoreHelperInterface      $coreHelper
    ) {
        parent::__construct($mongoConnection);
        $this->documentFactory = $documentFactory;
        $this->coreHelper = $coreHelper;
    }

    /**
     * @return mixed
     *
     * @todo Need to manage in more proper way
     */
    public function getDocumentClass(): string
    {
        return $this->coreHelper->getEWFAnnotationReaderInterface()->getDocumentClass($this);
    }

    protected function validate(array $document): bool
    {
        try {
            return $this->coreHelper->getEWFAnnotationReaderInterface()->validateData($document, $this);
        } catch (\Exception $e) {
            return true;
        }
    }

    protected function isConstExists(mixed $class, string $name): bool
    {
        if (is_object($class) || is_string($class)) {
            try {
                $reflect = new \ReflectionClass($class);

                return array_key_exists($name, $reflect->getConstants());
            } catch (ReflectionException $e) {
                // ignored
            }
        }

        return false;
    }

    public function getDocumentFactory(): DocumentFactoryInterface
    {
        return $this->documentFactory;
    }

    public function getNewDocument(array $data = []): BaseDocumentInterface
    {
        return $this->getDocumentFactory()->create($this->getDocumentClass(), $data);
    }

    /**
     * @throws \Exception
     */
    public function saveByField(
        string|null|array  $field,
        ArrayableInterface $document,
        array              $otherFilter = [],
        array              $otherOptions = []
    ): UpdateResult|InsertOneResult {
        //        $this->container->get('cache.app');
        $documentData = $document->toArray();

        if (is_string($field) && '_id' !== $field && !isset($documentData[$field])) {
            throw new \Exception('Unique Field Missing');
        } else if (is_array($field)) {
            foreach ($field as $key) {
                if ('_id' !== $key && !isset($documentData[$key])) {
                    throw new \Exception('Unique Field Missing');
                }
            }
        }

        if (
            $this->isConstExists($this->getDocumentClass(), 'KEY_CREATED_AT') &&
            !isset($documentData[$this->getDocumentClass()::KEY_CREATED_AT])
        ) {
            $documentData[$this->getDocumentClass()::KEY_CREATED_AT] = Carbon::now()->toDateTimeString();
        }

        if (
            $this->isConstExists($this->getDocumentClass(), 'KEY_UPDATED_AT') &&
            !isset($documentData[$this->getDocumentClass()::KEY_UPDATED_AT])
        ) {
            $documentData[$this->getDocumentClass()::KEY_UPDATED_AT] = Carbon::now()->toDateTimeString();
        }

        /*
         * Validate json format
         */
        if (!$this->validate($documentData)) {
            throw new \Exception('Json Validation Failed');
        }

        $options = array_merge(['upsert' => true], $otherOptions);

        if ('_id' === $field || in_array('_id', $field, true)) {
            if (!isset($documentData['_id'])) {
                return $this->getCollection()->insertOne($documentData);
            }

            $uuid = $documentData['_id'];
            unset($documentData['_id']);

            return $this->getCollection()
                ->updateOne(['_id' => new \MongoDB\BSON\ObjectId($uuid)], ['$set' => $documentData], $options);
        }

        $filter = $otherFilter;
        if (is_string($field)) {
            if (isset($documentData[$field])) {
                $filter[][$field] = $documentData[$field];
            }
        } elseif (is_array($field)) {
            foreach ($field as $key) {
                if (isset($documentData[$key])) {
                    $filter[][$key] = [
                        '$eq' => $documentData[$key],
                    ];
                }
            }
        }

        if (isset($documentData['_id'])) {
            unset($documentData['_id']);
        }

        if (is_array($filter) && !empty($filter)) {
            $filter = ['$and' => $filter];
        }

        return $this->getCollection()->updateOne($filter, ['$set' => $documentData], $options);
    }

    public function deleteByFilter(array $filter = []): object|array|null
    {
        return $this->getCollection()->findOneAndDelete($filter);
    }

    /**
     * @throws \Exception
     */
    public function save(
        ArrayableInterface $document,
        array              $otherFilter = [],
        array              $otherOptions = []
    ): UpdateResult|InsertOneResult {
        return $this->saveByField($this->getIndexNames(), $document, $otherFilter, $otherOptions);
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
            $items[] = $this->getDocumentFactory()->create($this->getDocumentClass(), $mongoItem->getArrayCopy());
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
            throw new \Exception('Document not found under ' . $this->collectionName);
        }

        return $this->getDocumentFactory()->create($this->getDocumentClass(), $mongoItem->getArrayCopy());
    }

    /**
     * @throws \Exception
     */
    public function findById(string $uuid): BaseDocumentInterface
    {
        /* @psalm-suppress UndefinedClass */
        return $this->findOne([
            '_id' => new \MongoDB\BSON\ObjectId($uuid),
        ]);
    }
}
