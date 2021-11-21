<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Command;

use EveryWorkflow\MongoBundle\Factory\DocumentFactoryInterface;
use EveryWorkflow\MongoBundle\Model\MigrationListInterface;
use EveryWorkflow\MongoBundle\Repository\MigrationRepositoryInterface;
use EveryWorkflow\MongoBundle\Support\MigrationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MongoMigrationRollbackCommand extends Command
{
    public const KEY_STEP = 'step';
    public const KEY_CLASS = 'class';

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
        $this->setDescription('Rollback mongo migration')
            ->setHelp('This command will rollback migration')
            ->addOption(self::KEY_STEP, 's', InputOption::VALUE_REQUIRED, 'Rollback step', 1)
            ->addOption(self::KEY_CLASS, 'c', InputOption::VALUE_REQUIRED, 'Rollback class');
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutput = new SymfonyStyle($input, $output);

        $inputOutput->title('Mongo migration rollback');

        $sortedMigrations = $this->migrationList->getSortedList();
        if (!count($sortedMigrations)) {
            $inputOutput->warning('No migration found!');

            return Command::FAILURE;
        }

        $step = (int) $input->getOption(self::KEY_STEP) ?? 1;
        $class = $input->getOption(self::KEY_CLASS) ?? false;

        if ($class && !empty($class)) {
            $migrationDocumentCollection = $this->migrationRepository->find(
                [
                    'class' => $class,
                ],
                [
                    'limit' => 1,
                ]
            );
        } else {
            $migrationDocumentCollection = $this->migrationRepository->find(
                [],
                [
                    'limit' => $step,
                    'sort' => [
                        'migrated_at' => -1,
                        '_id' => -1,
                    ],
                ]
            );
        }

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
            $inputOutput->success($result->getDeletedCount().' migration rollbacked.');

            return Command::SUCCESS;
        }

        $inputOutput->warning('Nothing to rollback! No migration seems to be migrated.');

        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    protected function rollbackMigration(
        SymfonyStyle $inputOutput,
        MigrationInterface $migration
    ): string {
        $class = get_class($migration);
        $inputOutput->text('- Running rollback '.$class);

        $migration->rollback();

        return $class;
    }
}
