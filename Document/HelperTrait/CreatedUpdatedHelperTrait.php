<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

namespace EveryWorkflow\MongoBundle\Document\HelperTrait;

use DateTime;
use EveryWorkflow\CoreBundle\Validation\Type\DateTimeValidation;

trait CreatedUpdatedHelperTrait
{
    #[DateTimeValidation(required: true)]
    public function setCreatedAt(DateTime|string $createdAt): self
    {
        if ($createdAt instanceof DateTime) {
            $createdAt = $createdAt->format(DateTime::ISO8601);
        }
        $this->dataObject->setData(CreatedUpdatedHelperTraitInterface::KEY_CREATED_AT, $createdAt);

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        $createdAt = $this->dataObject->getData(CreatedUpdatedHelperTraitInterface::KEY_CREATED_AT);
        if ($createdAt) {
            return new DateTime($createdAt);
        }

        return null;
    }

    #[DateTimeValidation(required: true)]
    public function setUpdatedAt(DateTime|string $updatedAt): self
    {
        if ($updatedAt instanceof DateTime) {
            $updatedAt = $updatedAt->format(DateTime::ISO8601);
        }
        $this->dataObject->setData(CreatedUpdatedHelperTraitInterface::KEY_UPDATED_AT, $updatedAt);

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        $updatedAt = $this->dataObject->getData(CreatedUpdatedHelperTraitInterface::KEY_UPDATED_AT);
        if ($updatedAt) {
            return new DateTime($updatedAt);
        }

        return null;
    }
}
