<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\CoreBundle\Support\ArrayableInterface;
use EveryWorkflow\MongoBundle\Document\BaseDocumentInterface;
use EveryWorkflow\MongoBundle\Factory\DocumentFactoryInterface;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;

interface BaseDocumentRepositoryInterface extends BaseRepositoryInterface
{
    public function getDocumentFactory(): DocumentFactoryInterface;

    public function getNewDocument(array $data = []): BaseDocumentInterface;

    public function saveByField(
        string|null|array  $field,
        ArrayableInterface $document,
        array              $otherFilter = [],
        array              $otherOptions = []
    ): UpdateResult|InsertOneResult;

    public function deleteByFilter(array $filter = []): object|array|null;

    /**
     * @throws \Exception
     */
    public function save(
        ArrayableInterface $document,
        array              $otherFilter = [],
        array              $otherOptions = []
    ): UpdateResult|InsertOneResult;

    /**
     * @return BaseDocumentInterface[]
     *
     * @throws \ReflectionException
     */
    public function find(array $filter = [], array $options = []): array;

    /**
     * @throws \ReflectionException
     */
    public function findOne(array $filter = [], array $options = []): BaseDocumentInterface;

    /**
     * @throws \Exception
     */
    public function findById(string $uuid): BaseDocumentInterface;
}
