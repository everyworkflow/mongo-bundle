<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Command;

use EveryWorkflow\MongoBundle\Repository\MigrationRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MongoMigrationLogCommand extends Command
{
    protected static $defaultName = 'mongo:migration:log';

    protected MigrationRepositoryInterface $migrationRepository;

    public function __construct(
        MigrationRepositoryInterface $migrationRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->migrationRepository = $migrationRepository;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Shows mongo migration log')
            ->setHelp('This command will print mongo migrations');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputOutput = new SymfonyStyle($input, $output);

        $inputOutput->title('Mongo migration log');

        $table = new Table($output);
        $table->setHeaders(['UUID', 'Bundle Name', 'File Name', 'Migration Class', 'Migrated At']);

        /* Adding migration data to table format */
        $migrations = $this->migrationRepository->find();
        foreach ($migrations as $migration) {
            $table->addRow([
                $migration->getId(),
                $migration->getBundleName(),
                $migration->getFileName(),
                $migration->getClass(),
                $migration->getMigratedAt(),
            ]);
        }

        $table->render();
        $inputOutput->newLine();

        return Command::SUCCESS;
    }
}
