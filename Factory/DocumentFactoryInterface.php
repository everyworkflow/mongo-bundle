<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Factory;

interface DocumentFactoryInterface
{
    /**
     * @param string $className
     * @param array $data
     * @return mixed
     */
    public function create(string $className, array $data = []): mixed;
}
