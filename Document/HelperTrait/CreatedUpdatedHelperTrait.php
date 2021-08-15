<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

namespace EveryWorkflow\MongoBundle\Document\HelperTrait;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use EveryWorkflow\CoreBundle\Annotation\EWFDataTypes;

trait CreatedUpdatedHelperTrait
{
    /**
     * @EWFDataTypes (type="datetime", mongofield=CreatedUpdatedHelperTraitInterface::KEY_CREATED_AT, required=TRUE)
     */
    public function setCreatedAt(CarbonInterface $createdAt): self
    {
        $this->dataObject->setData(CreatedUpdatedHelperTraitInterface::KEY_CREATED_AT, $createdAt->toDateTimeString());
        return $this;
    }

    public function getCreatedAt(): ?CarbonInterface
    {
        $createdAt = $this->dataObject->getData(CreatedUpdatedHelperTraitInterface::KEY_CREATED_AT);
        if ($createdAt) {
            return Carbon::make($createdAt);
        }
        return null;
    }

    /**
     * @param CarbonInterface $createdAt
     * @EWFDataTypes (type="datetime", mongofield=CreatedUpdatedHelperTraitInterface::KEY_UPDATED_AT, required=TRUE)
     */
    public function setUpdatedAt(CarbonInterface $updatedAt): CreatedUpdatedHelperTraitInterface
    {
        $this->dataObject->setData(CreatedUpdatedHelperTraitInterface::KEY_UPDATED_AT, $updatedAt->toDateTimeString());
        return $this;
    }

    public function getUpdatedAt(): ?CarbonInterface
    {
        $updatedAt = $this->dataObject->getData(CreatedUpdatedHelperTraitInterface::KEY_UPDATED_AT);
        if ($updatedAt) {
            return Carbon::make($updatedAt);
        }
        return null;
    }
}
