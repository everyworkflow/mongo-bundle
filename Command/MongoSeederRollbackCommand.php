<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Command;

use EveryWorkflow\MongoBundle\Factory\DocumentFactoryInterface;
use EveryWorkflow\MongoBundle\Model\SeederListInterface;
use EveryWorkflow\MongoBundle\Repository\SeederRepositoryInterface;
use EveryWorkflow\MongoBundle\Support\SeederInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MongoSeederRollbackCommand extends Command
{
    public const KEY_STEP = 'step';
    public const KEY_CLASS = 'class';

    protected static $defaultName = 'mongo:seeder:rollback';

    protected SeederListInterface $seederList;
    protected DocumentFactoryInterface $documentFactory;
    protected SeederRepositoryInterface $seederRepository;

    public function __construct(
        SeederListInterface $seederList,
        DocumentFactoryInterface $documentFactory,
        SeederRepositoryInterface $seederRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->seederList = $seederList;
        $this->documentFactory = $documentFactory;
        $this->seederRepository = $seederRepository;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Rollback last mongo seeder')
            ->setHelp('This command will rollback last seeder')
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

        $inputOutput->title('Mongo seeder rollback');

        $sortedSeeders = $this->seederList->getSortedList();
        if (!count($sortedSeeders)) {
            $inputOutput->warning('No seeder found!');

            return Command::FAILURE;
        }

        $step = (int) $input->getOption(self::KEY_STEP) ?? 1;
        $class = $input->getOption(self::KEY_CLASS) ?? false;

        if ($class && !empty($class)) {
            $seederDocumentCollection = $this->seederRepository->find(
                [
                    'class' => $class,
                ],
                [
                    'limit' => 1,
                ]
            );
        } else {
            $seederDocumentCollection = $this->seederRepository->find(
            [],
            [
                'limit' => $step,
                'sort' => [
                    'seeded_at' => -1,
                    '_id' => -1,
                ],
            ]
        );
        }
        $actionableSeederClass = [];
        foreach ($seederDocumentCollection as $seederDocument) {
            if (isset($sortedSeeders[$seederDocument->getClass()])) {
                try {
                    $actionableSeederClass[] = $this->rollbackSeeder(
                        $inputOutput,
                        $sortedSeeders[$seederDocument->getClass()]
                    );
                } catch (\Exception $e) {
                    $inputOutput->error($e->getMessage());
                }
                $inputOutput->newLine();
            }
        }

        if (count($actionableSeederClass)) {
            $inputOutput->newLine();
            $result = $this->seederRepository->getCollection()
                ->deleteMany(['class' => ['$in' => $actionableSeederClass]]);
            $inputOutput->success($result->getDeletedCount().' seeder rollbacked.');

            return Command::SUCCESS;
        }

        $inputOutput->warning('Nothing to rollback! No seeder seems to be seeded.');

        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    protected function rollbackSeeder(
        SymfonyStyle $inputOutput,
        SeederInterface $seeder
    ): string {
        $class = get_class($seeder);
        $inputOutput->text('- Running rollback '.$class);

        $seeder->rollback();

        return $class;
    }
}
