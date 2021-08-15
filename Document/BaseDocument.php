<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Document;

use EveryWorkflow\CoreBundle\Model\DataObjectInterface;

class BaseDocument implements BaseDocumentInterface
{
    protected DataObjectInterface $dataObject;

    public function __construct(DataObjectInterface $dataObject)
    {
        $this->dataObject = $dataObject;
    }

    public function setId(string $id): self
    {
        $this->dataObject->setData(self::KEY_ID, $id);

        return $this;
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

    public function toArray(): array
    {
        $arrayData = $this->dataObject->toArray();
        if (isset($arrayData['_id'])) {
            $arrayData['_id'] = $this->getId();
        }

        return $arrayData;
    }

    public function setData($field, $value = null): self
    {
        if (is_array($field)) {
            foreach ($field as $item => $value) {
                $this->dataObject->setData($item, $value);
            }
        } else {
            $this->dataObject->setData($field, $value);
        }

        return $this;
    }

    public function getData($field = null)
    {
        if ($field) {
            return $this->dataObject->getData($field);
        }

        return $this->dataObject->toArray();
    }
}
