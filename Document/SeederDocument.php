<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Document;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use EveryWorkflow\CoreBundle\Model\DataObjectInterface;

class SeederDocument implements SeederDocumentInterface
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

    /**
     * @param string $bundleName
     * @return $this
     * @EWFDataTypes (type="string", mongofield=self::KEY_BUNDLE_NAME, required=TRUE)
     */
    public function setBundleName(string $bundleName): self
    {
        $this->dataObject->setData(self::KEY_BUNDLE_NAME, $bundleName);
        return $this;
    }

    public function getBundleName(): string
    {
        return $this->dataObject->getData(self::KEY_BUNDLE_NAME);
    }

    /**
     * @param string $fileName
     * @return $this
     * @EWFDataTypes (type="string", mongofield=self::KEY_FILE_NAME, required=TRUE)
     */
    public function setFileName(string $fileName): self
    {
        $this->dataObject->setData(self::KEY_FILE_NAME, $fileName);
        return $this;
    }

    public function getFileName(): string
    {
        return $this->dataObject->getData(self::KEY_FILE_NAME);
    }

    /**
     * @param string $class
     * @return $this
     * @EWFDataTypes (type="string", mongofield=self::KEY_CLASS, required=TRUE)
     */
    public function setClass(string $class): self
    {
        $this->dataObject->setData(self::KEY_CLASS, $class);
        return $this;
    }

    public function getClass(): string
    {
        return $this->dataObject->getData(self::KEY_CLASS);
    }

    /**
     * @param CarbonInterface $seededAt
     * @EWFDataTypes (type="datetime", mongofield=CreatedUpdatedHelperTraitInterface::KEY_SEEDED_AT, required=TRUE)
     * @return $this
     */
    public function setSeededAt(CarbonInterface $seededAt): self
    {
        $this->dataObject->setData(self::KEY_SEEDED_AT, $seededAt->toDateTimeString());
        return $this;
    }

    public function getSeededAt(): ?CarbonInterface
    {
        $seededAt = $this->dataObject->getData(self::KEY_SEEDED_AT);
        if ($seededAt) {
            return Carbon::make($seededAt);
        }
        return null;
    }

    public function toArray(): array
    {
        return $this->dataObject->toArray();
    }
}
