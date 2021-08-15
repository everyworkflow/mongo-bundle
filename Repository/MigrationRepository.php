<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\CoreBundle\Model\DataObjectFactory;
use EveryWorkflow\MongoBundle\Document\MigrationDocument;
use EveryWorkflow\MongoBundle\Document\MigrationDocumentInterface;
use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;

class MigrationRepository extends BaseRepository implements MigrationRepositoryInterface
{
    protected string $collectionName = 'migration_collection';

    protected DataObjectFactory $dataObjectFactory;

    public function __construct(
        DataObjectFactory $dataObjectFactory,
        MongoConnectionInterface $mongoConnection
    ) {
        parent::__construct($mongoConnection);
        $this->dataObjectFactory = $dataObjectFactory;
    }

    public function mapMigration(array $data): MigrationDocumentInterface
    {
        $dataObj = $this->dataObjectFactory->create($data);

        return new MigrationDocument($dataObj);
    }

    /**
     * @return MigrationDocumentInterface[]
     */
    public function find(array $filters = [], array $options = []): array
    {
        $migrations = [];
        $migrationData = $this->getCollection()->find($filters, $options);
        /** @var \MongoDB\Model\BSONDocument $migrationDatum */
        foreach ($migrationData as $migrationDatum) {
            $migrations[] = $this->mapMigration($migrationDatum->getArrayCopy());
        }

        return $migrations;
    }

    /**
     * @param MigrationDocumentInterface[]
     * @return \MongoDB\InsertManyResult
     */
    public function insertMany(array $migrations): \MongoDB\InsertManyResult
    {
        $data = [];
        /** @var MigrationDocumentInterface $migration */
        foreach ($migrations as $migration) {
            $data[] = $migration->toArray();
        }

        return $this->getCollection()->insertMany($data);
    }
}
