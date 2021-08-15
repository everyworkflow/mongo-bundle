<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Command;

use EveryWorkflow\MongoBundle\Model\MigrationListInterface;
use EveryWorkflow\MongoBundle\Support\MigrationInterface;
use EveryWorkflow\MongoBundle\Factory\DocumentFactoryInterface;
use EveryWorkflow\MongoBundle\Repository\MigrationRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MongoMigrationRollbackCommand extends Command
{
    protected static $defaultName = 'mongo:migration:rollback';

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
        $this->setDescription('Rollback last mongo migration')
            ->setHelp('This command will rollback last migration');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutput = new SymfonyStyle($input, $output);

        $inputOutput->title('Mongo migration rollback');

        $sortedMigrations = $this->migrationList->getSortedMigrations();
        if (!count($sortedMigrations)) {
            $inputOutput->warning('No migration found!');
            return Command::FAILURE;
        }

        $migrationDocumentCollection = $this->migrationRepository->find(
            [],
            [
                'limit' => 1,
                'sort' => [
                    'migrated_at' => -1,
                    '_id' => -1,
                ],
            ]
        );
        $actionableMigrationClass = [];
        foreach ($migrationDocumentCollection as $migrationDocument) {
            if (isset($sortedMigrations[$migrationDocument->getClass()])) {
                try {
                    $actionableMigrationClass[] = $this->rollbackMigration(
                        $inputOutput,
                        $sortedMigrations[$migrationDocument->getClass()]
                    );
                } catch (\Exception $e) {
                    $inputOutput->error($e->getMessage());
                }
                $inputOutput->newLine();
            }
        }

        if (count($actionableMigrationClass)) {
            $inputOutput->newLine();
            $result = $this->migrationRepository->getCollection()
                ->deleteMany(['class' => ['$in' => $actionableMigrationClass]]);
            $inputOutput->success($result->getDeletedCount() . ' migration rollbacked.');

            return Command::SUCCESS;
        }

        $inputOutput->warning('Nothing to rollback! No migration seems to be migrated.');

        return Command::SUCCESS;
    }

    /**
     * @param SymfonyStyle $inputOutput
     * @param MigrationInterface $migration
     * @return string
     * @throws \Exception
     */
    protected function rollbackMigration(
        SymfonyStyle $inputOutput,
        MigrationInterface $migration
    ): string {

        $class = get_class($migration);
        $inputOutput->text('- Running rollback ' . $class);

        $migration->rollback();
        return $class;
    }
}
