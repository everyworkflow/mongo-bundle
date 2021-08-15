<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Document\HelperTrait;

use Carbon\CarbonInterface;

interface CreatedUpdatedHelperTraitInterface
{
    public const KEY_CREATED_AT = 'created_at';
    public const KEY_UPDATED_AT = 'updated_at';

    public function setCreatedAt(CarbonInterface $createdAt): self;

    public function getCreatedAt(): ?CarbonInterface;

    public function setUpdatedAt(CarbonInterface $updatedAt): self;

    public function getUpdatedAt(): ?CarbonInterface;
}
