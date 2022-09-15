<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Document;

use EveryWorkflow\CoreBundle\Support\ArrayableInterface;

interface BaseDocumentInterface extends ArrayableInterface
{
    public const KEY_ID = '_id';

    public function getId(): ?string;

    public function setData($field, $value = null): self;

    public function getData($field = null);

    /**
     * @param string $key
     * @return self
     */
    public function unsetData(string $key): self;
}
