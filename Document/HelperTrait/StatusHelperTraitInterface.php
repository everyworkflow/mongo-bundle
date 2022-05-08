<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Document\HelperTrait;

interface StatusHelperTraitInterface
{
    public const STATUS_ENABLE = 'enable';
    public const STATUS_DISABLE = 'disable';

    public const KEY_STATUS = 'status';

    public function setStatus(string $status): self;

    public function getStatus(): string;
}
