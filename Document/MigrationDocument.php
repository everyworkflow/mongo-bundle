<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Document;

use DateTime;
use EveryWorkflow\CoreBundle\Model\DataObjectInterface;
use EveryWorkflow\CoreBundle\Validation\Type\DateTimeValidation;
use EveryWorkflow\CoreBundle\Validation\Type\StringValidation;

class MigrationDocument implements MigrationDocumentInterface
{
    protected DataObjectInterface $dataObject;

    public function __construct(DataObjectInterface $dataObject)
    {
        $this->dataObject = $dataObject;
    }

    public function getId(): ?string
    {
        /** @var \MongoDB\BSON\ObjectId $uuid */
        $uuid = $this->dataObject->getData(self::KEY_ID);
        if (is_string($uuid)) {
            return $uuid;
        }
        if ($uuid instanceof \MongoDB\BSON\ObjectId) {
            return (string) $uuid;
        }
        return null;
    }

    #[StringValidation(required: true)]
    public function setBundleName(string $bundleName): self
    {
        $this->dataObject->setData(self::KEY_BUNDLE_NAME, $bundleName);
        return $this;
    }

    public function getBundleName(): string
    {
        return $this->dataObject->getData(self::KEY_BUNDLE_NAME);
    }

    #[StringValidation(required: true)]
    public function setFileName(string $fileName): self
    {
        $this->dataObject->setData(self::KEY_FILE_NAME, $fileName);
        return $this;
    }

    public function getFileName(): string
    {
        return $this->dataObject->getData(self::KEY_FILE_NAME);
    }

    #[StringValidation(required: true)]
    public function setClass(string $class): self
    {
        $this->dataObject->setData(self::KEY_CLASS, $class);
        return $this;
    }

    public function getClass(): string
    {
        return $this->dataObject->getData(self::KEY_CLASS);
    }

    #[DateTimeValidation(required: true)]
    public function setMigratedAt(DateTime|string $migratedAt): self
    {
        if ($migratedAt instanceof DateTime) {
            $migratedAt = $migratedAt->format(DateTime::ISO8601);
        }
        $this->dataObject->setData(self::KEY_MIGRATED_AT, $migratedAt);
        
        return $this;
    }

    public function getMigratedAt(): ?DateTime
    {
        $migratedAt = $this->dataObject->getData(self::KEY_MIGRATED_AT);
        if ($migratedAt) {
            return new DateTime($migratedAt);
        }

        return null;
    }

    public function toArray(): array
    {
        return $this->dataObject->toArray();
    }
}
