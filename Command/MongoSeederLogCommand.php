<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Command;

use EveryWorkflow\MongoBundle\Repository\SeederRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MongoSeederLogCommand extends Command
{
    protected static $defaultName = 'mongo:seeder:log';

    protected SeederRepositoryInterface $seederRepository;

    public function __construct(
        SeederRepositoryInterface $seederRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->seederRepository = $seederRepository;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Shows mongo seeder log')
            ->setHelp('This command will print mongo seeders');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputOutput = new SymfonyStyle($input, $output);

        $inputOutput->title('Mongo seeder log');

        $table = new Table($output);
        $table->setHeaders(['UUID', 'Bundle Name', 'File Name', 'Seeder Class', 'Seeded At']);

        /* Adding seeder data to table format */
        $seeders = $this->seederRepository->find();
        foreach ($seeders as $seeder) {
            $table->addRow([
                $seeder->getId(),
                $seeder->getBundleName(),
                $seeder->getFileName(),
                $seeder->getClass(),
                $seeder->getSeededAt(),
            ]);
        }

        $table->render();
        $inputOutput->newLine();

        return Command::SUCCESS;
    }
}
