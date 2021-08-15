<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

namespace EveryWorkflow\MongoBundle\Document\HelperTrait;

use EveryWorkflow\CoreBundle\Annotation\EWFDataTypes;

trait StatusHelperTrait
{
    /**
     * @EWFDataTypes (type="string", mongofield=StatusHelperTraitInterface::KEY_STATUS, required=TRUE)
     */
    public function setStatus(string $status): self
    {
        $this->dataObject->setData(StatusHelperTraitInterface::KEY_STATUS, $status);

        return $this;
    }

    public function getStatus(): bool
    {
        return (bool) $this->dataObject->getData(StatusHelperTraitInterface::KEY_STATUS);
    }
}
