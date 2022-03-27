<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Repository;

use EveryWorkflow\CoreBundle\Exception\ValidatorException;
use EveryWorkflow\CoreBundle\Support\ArrayableInterface;
use EveryWorkflow\MongoBundle\Document\BaseDocumentInterface;
use EveryWorkflow\MongoBundle\Exception\PrimaryKeyMissingException;
use EveryWorkflow\MongoBundle\Support\Attribute\RepositoryAttribute;
use MongoDB\UpdateResult;

interface BaseDocumentRepositoryInterface extends BaseRepositoryInterface
{
    public function setRepositoryAttribute(RepositoryAttribute $repositoryAttribute): self;

    public function getRepositoryAttribute(): ?RepositoryAttribute;

    public function setDocumentClass(string $documentClass): self;

    public function getDocumentClass(): ?string;

    public function getEventPrefix(): string;
    
    public function create(array $data = []): BaseDocumentInterface;

    public function deleteOneByFilter(array $filter = []): object|array|null;

    public function deleteByFilter(array $filter = []): object|array|null;

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function saveOne(
        ArrayableInterface $document,
        array $otherFilter = [],
        array $otherOptions = []
    ): BaseDocumentInterface;

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     */
    public function insertOne(ArrayableInterface $document): BaseDocumentInterface;

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function updateOne(
        ArrayableInterface $document,
        array $otherFilter = [],
        array $otherOptions = []
    ): BaseDocumentInterface;

    /**
     * @throws PrimaryKeyMissingException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function replaceOne(
        ArrayableInterface $document,
        array $otherFilter = [],
        array $otherOptions = []
    ): BaseDocumentInterface;

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
    public function findById(string | \MongoDB\BSON\ObjectId $uuid): BaseDocumentInterface;

    public function bulkUpdateByIds(
        array $ids = [],
        array $updateData = [],
        array $options = []
    ): UpdateResult;
}
