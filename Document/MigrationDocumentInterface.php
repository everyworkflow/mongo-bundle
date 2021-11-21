<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Document;

use Carbon\CarbonInterface;
use EveryWorkflow\CoreBundle\Support\ArrayableInterface;

interface MigrationDocumentInterface extends ArrayableInterface
{
    public const KEY_ID = '_id';
    public const KEY_BUNDLE_NAME = 'bundle_name';
    public const KEY_FILE_NAME = 'file_name';
    public const KEY_CLASS = 'class';
    public const KEY_MIGRATED_AT = 'migrated_at';

    public function getId(): ?string;

    public function setBundleName(string $bundleName): self;

    public function getBundleName(): string;

    public function setFileName(string $fileName): self;

    public function getFileName(): string;

    public function setClass(string $class): self;

    public function getClass(): string;

    public function setMigratedAt(CarbonInterface $migratedAt): self;

    public function getMigratedAt(): ?CarbonInterface;
}
