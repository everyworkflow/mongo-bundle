<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Command;

use EveryWorkflow\MongoBundle\Model\MongoConnectionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MongoDatabaseDropCommand extends Command
{
    protected static $defaultName = 'mongo:database:drop';
    /**
     * @var MongoConnectionInterface
     */
    protected MongoConnectionInterface $mongoConnection;
    protected string $mongoDb;

    public function __construct(MongoConnectionInterface $mongoConnection, string $mongoDb, string $name = null)
    {
        parent::__construct($name);
        $this->mongoConnection = $mongoConnection;
        $this->mongoDb = $mongoDb;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Drop mongo database')
            ->setHelp('This command drop mongo database');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputOutput = new SymfonyStyle($input, $output);
        $this->mongoConnection->getClient()->dropDatabase($this->mongoDb);
        $inputOutput->text('Drop database: ' . $this->mongoDb);
        return Command::SUCCESS;
    }
}
