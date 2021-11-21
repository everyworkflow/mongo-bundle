<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\CoreBundle\Model\DataObjectFactoryInterface;
use EveryWorkflow\MongoBundle\Document\SeederDocument;
use EveryWorkflow\MongoBundle\Document\SeederDocumentInterface;
use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;

class SeederRepository extends BaseRepository implements SeederRepositoryInterface
{
    protected string $collectionName = 'seeder_collection';

    protected DataObjectFactoryInterface $dataObjectFactory;

    public function __construct(
        DataObjectFactoryInterface $dataObjectFactory,
        MongoConnectionInterface $mongoConnection
    ) {
        parent::__construct($mongoConnection);
        $this->dataObjectFactory = $dataObjectFactory;
    }

    public function mapDocument(array $data): SeederDocumentInterface
    {
        $dataObj = $this->dataObjectFactory->create($data);

        return new SeederDocument($dataObj);
    }

    /**
     * @return SeederDocumentInterface[]
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
     * @param SeederDocumentInterface[]
     * @return \MongoDB\InsertManyResult
     */
    public function insertMany(array $items): \MongoDB\InsertManyResult
    {
        $data = [];
        /** @var SeederDocumentInterface $item */
        foreach ($items as $item) {
            $data[] = $item->toArray();
        }

        return $this->getCollection()->insertMany($data);
    }
}
