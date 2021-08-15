<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Factory;

use EveryWorkflow\CoreBundle\Helper\CoreHelperInterface;
use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;
use EveryWorkflow\MongoBundle\Repository\BaseDocumentRepository;
use EveryWorkflow\MongoBundle\Repository\BaseDocumentRepositoryInterface;

class DocumentRepositoryFactory implements DocumentRepositoryFactoryInterface
{
    protected MongoConnectionInterface $mongoConnection;
    protected DocumentFactoryInterface $documentFactory;
    protected CoreHelperInterface $coreHelper;

    public function __construct(
        MongoConnectionInterface $mongoConnection,
        DocumentFactoryInterface $documentFactory,
        CoreHelperInterface $coreHelper
    ) {
        $this->mongoConnection = $mongoConnection;
        $this->documentFactory = $documentFactory;
        $this->coreHelper = $coreHelper;
    }

    public function create(): BaseDocumentRepositoryInterface
    {
        return new BaseDocumentRepository($this->mongoConnection, $this->documentFactory, $this->coreHelper);
    }
}
