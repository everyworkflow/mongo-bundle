<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Factory;

use EveryWorkflow\CoreBundle\Model\DataObjectFactoryInterface;

class DocumentFactory implements DocumentFactoryInterface
{
    /**
     * @var DataObjectFactoryInterface
     */
    protected DataObjectFactoryInterface $dataObjectFactory;

    public function __construct(DataObjectFactoryInterface $dataObjectFactory)
    {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param string $className
     * @param array $data
     * @return mixed
     */
    public function create(string $className, array $data = []): mixed
    {
        $dataObj = $this->dataObjectFactory->create($data);
        return new $className($dataObj);
    }
}
