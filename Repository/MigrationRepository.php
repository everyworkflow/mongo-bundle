<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\CoreBundle\Model\DataObjectFactoryInterface;
use EveryWorkflow\MongoBundle\Document\MigrationDocument;
use EveryWorkflow\MongoBundle\Document\MigrationDocumentInterface;
use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;

class MigrationRepository extends BaseRepository implements MigrationRepositoryInterface
{
    public function __construct(
        protected DataObjectFactoryInterface $dataObjectFactory,
        protected MongoConnectionInterface $mongoConnection,
        protected string $collectionName = 'migration_collection',
        protected string|array $primaryKey = ''
    ) {
        parent::__construct($mongoConnection, $collectionName, $primaryKey);
        $this->dataObjectFactory = $dataObjectFactory;
    }

    public function mapDocument(array $data): MigrationDocumentInterface
    {
        $dataObj = $this->dataObjectFactory->create($data);

        return new MigrationDocument($dataObj);
    }

    /**
     * @return MigrationDocumentInterface[]
     */
    public function find(array $filters = [], array $options = []): array
    {
        $documents = [];
        $items = $this->getCollection()->find($filters, $options);
        /** @var \MongoDB\Model\BSONDocument $item */
        foreach ($items as $item) {
            $documents[] = $this->mapDocument($item->getArrayCopy());
        }

        return $documents;
    }

    /**
     * @param MigrationDocumentInterface[]
     * @return \MongoDB\InsertManyResult
     */
    public function insertMany(array $items): \MongoDB\InsertManyResult
    {
        $data = [];
        /** @var MigrationDocumentInterface $item */
        foreach ($items as $item) {
            $data[] = $item->toArray();
        }

        return $this->getCollection()->insertMany($data);
    }
}
