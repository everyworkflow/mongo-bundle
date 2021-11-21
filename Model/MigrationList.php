<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Model;

use EveryWorkflow\MongoBundle\Support\MigrationInterface;

class MigrationList implements MigrationListInterface
{
    /**
     * All the migration types, injected via service.
     */
    protected iterable $migrations;

    public function __construct(iterable $migrations)
    {
        $this->migrations = $migrations;
    }

    /**
     * @return MigrationInterface[]
     */
    public function getSortedList(): array
    {
        $sortedMigrationNames = [];

        $migrations = [];
        foreach ($this->migrations as $migration) {
            if ($migration instanceof MigrationInterface) {
                $class = get_class($migration);
                $classNameArr = explode('\\', $class);
                $fileName = $classNameArr[count($classNameArr) - 1];
                $sortedMigrationNames[$fileName][] = $class;
                $migrations[$class] = $migration;
            }
        }

        ksort($sortedMigrationNames);

        $sortedMigrations = [];
        foreach ($sortedMigrationNames as $fileName => $classes) {
            foreach ($classes as $class) {
                $sortedMigrations[$class] = $migrations[$class];
            }
        }

        return $sortedMigrations;
    }
}
