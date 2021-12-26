<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Document\HelperTrait;

use DateTime;

interface CreatedUpdatedHelperTraitInterface
{
    public const KEY_CREATED_AT = 'created_at';
    public const KEY_UPDATED_AT = 'updated_at';

    public function setCreatedAt(DateTime|string $createdAt): self;

    public function getCreatedAt(): ?DateTime;

    public function setUpdatedAt(DateTime|string $updatedAt): self;

    public function getUpdatedAt(): ?DateTime;
}
