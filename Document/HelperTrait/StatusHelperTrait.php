<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

namespace EveryWorkflow\MongoBundle\Document\HelperTrait;

use EveryWorkflow\CoreBundle\Validation\Type\StringValidation;

trait StatusHelperTrait
{
    #[StringValidation(default: 'disable')]
    public function setStatus(string $status): self
    {
        $this->dataObject->setData(StatusHelperTraitInterface::KEY_STATUS, $status);

        return $this;
    }

    public function getStatus(): string
    {
        return $this->dataObject->getData(StatusHelperTraitInterface::KEY_STATUS) ?? 'disable';
    }
}
