<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Command;

use Carbon\Carbon;
use EveryWorkflow\MongoBundle\Document\MigrationDocument;
use EveryWorkflow\MongoBundle\Document\MigrationDocumentInterface;
use EveryWorkflow\MongoBundle\Model\MigrationListInterface;
use EveryWorkflow\MongoBundle\Repository\MigrationRepositoryInterface;
use EveryWorkflow\MongoBundle\Factory\DocumentFactoryInterface;
use EveryWorkflow\MongoBundle\Support\MigrationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MongoMigrateCommand extends Command
{
    protected static $defaultName = 'mongo:migrate';

    protected MigrationListInterface $migrationList;
    protected DocumentFactoryInterface $documentFactory;
    protected MigrationRepositoryInterface $migrationRepository;

    public function __construct(
        MigrationListInterface $migrationList,
        DocumentFactoryInterface $documentFactory,
        MigrationRepositoryInterface $migrationRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->migrationList = $migrationList;
        $this->documentFactory = $documentFactory;
        $this->migrationRepository = $migrationRepository;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Migrate mongo migrations')
            ->setHelp('This command will migrate mongo migrations');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutput = new SymfonyStyle($input, $output);

        $inputOutput->title('EveryWorkflow Data Migrate');

        $sortedMigrations = $this->migrationList->getSortedList();
        if (!count($sortedMigrations)) {
            $inputOutput->warning('No migration found!');
            return Command::FAILURE;
        }

        $migrationCollection = $this->migrationRepository->getCollection()->find();
        $newMigrations = [];
        $migrationClasses = array_column($migrationCollection->toArray(), null, MigrationDocument::KEY_CLASS);

        foreach ($sortedMigrations as $migration) {
            $class = get_class($migration);

            /* If new migration then run ->migration() and store log */
            if (!isset($migrationClasses[$class])) {
                try {
                    $newMigrations[] = $this->migrateMigration($inputOutput, $migration);
                } catch (\Exception $e) {
                    $inputOutput->warning($e->getMessage());
                }
            }
        }

        if ($newMigrations) {
            $inputOutput->newLine();
            $result = $this->migrationRepository->insertMany($newMigrations);
            $inputOutput->success($result->getInsertedCount() . ' migrations are migrated.');

            foreach ($newMigrations as $migration) {
                $inputOutput->text('- Migrated ' . $migration->getClass());
            }

            $inputOutput->newLine();
            return Command::SUCCESS;
        }

        $inputOutput->success('Nothing to migrate! Everything seems updated.');

        return Command::SUCCESS;
    }

    /**
     * @param SymfonyStyle $inputOutput
     * @param MigrationInterface $migration
     * @return MigrationDocumentInterface
     * @throws \Exception
     */
    protected function migrateMigration(
        SymfonyStyle $inputOutput,
        MigrationInterface $migration
    ): MigrationDocumentInterface {
        $class = get_class($migration);
        $classNameArray = explode('\\', $class);
        $inputOutput->text('- Running migration ' . $class);

        try {
            $migrationStatus = $migration->migrate();
        } catch (\Exception $e) {
            try {
                $migration->rollback();
            } catch (\Exception $e) {
                // ignore if rollback fails while migrations
            }
            $migrationStatus = false;
        }

        if (!$migrationStatus) {
            throw new \Exception('Migration failed for ' . $classNameArray[count($classNameArray) - 1]);
        }

        /** @var MigrationDocumentInterface $migrationDocument */
        $migrationDocument = $this->documentFactory->create(MigrationDocument::class, [
            'class' => $class,
        ]);

        $bundleNameArray = [];
        foreach ($classNameArray as $str) {
            if ('Migration' === $str) {
                break;
            }
            $bundleNameArray[] = ucfirst($str);
        }

        return $migrationDocument
            ->setBundleName(implode('_', $bundleNameArray))
            ->setFileName($classNameArray[count($classNameArray) - 1])
            ->setMigratedAt(Carbon::now());
    }
}
