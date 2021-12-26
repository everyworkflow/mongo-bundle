<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Command;

use EveryWorkflow\CoreBundle\Model\SystemDateTimeInterface;
use EveryWorkflow\MongoBundle\Document\SeederDocument;
use EveryWorkflow\MongoBundle\Document\SeederDocumentInterface;
use EveryWorkflow\MongoBundle\Factory\DocumentFactoryInterface;
use EveryWorkflow\MongoBundle\Model\SeederListInterface;
use EveryWorkflow\MongoBundle\Repository\SeederRepositoryInterface;
use EveryWorkflow\MongoBundle\Support\SeederInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MongoSeedCommand extends Command
{
    protected static $defaultName = 'mongo:seed';

    protected SeederListInterface $seederList;
    protected DocumentFactoryInterface $documentFactory;
    protected SeederRepositoryInterface $seederRepository;
    protected SystemDateTimeInterface $systemDateTime;

    public function __construct(
        SeederListInterface $seederList,
        DocumentFactoryInterface $documentFactory,
        SeederRepositoryInterface $seederRepository,
        SystemDateTimeInterface $systemDateTime,
        string $name = null
    ) {
        parent::__construct($name);
        $this->seederList = $seederList;
        $this->documentFactory = $documentFactory;
        $this->seederRepository = $seederRepository;
        $this->systemDateTime = $systemDateTime;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Seed mongo seeders')
            ->setHelp('This command will seed mongo seeders');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutput = new SymfonyStyle($input, $output);

        $inputOutput->title('EveryWorkflow Data Seed');

        $sortedSeeders = $this->seederList->getSortedList();
        if (!count($sortedSeeders)) {
            $inputOutput->warning('No seeders found!');
            return Command::FAILURE;
        }

        $seederCollection = $this->seederRepository->getCollection()->find();
        $newSeeders = [];
        $seederClasses = array_column($seederCollection->toArray(), null, SeederDocument::KEY_CLASS);

        foreach ($sortedSeeders as $seeder) {
            $class = get_class($seeder);

            /* If new seeder then run ->seed() and store log */
            if (!isset($seederClasses[$class])) {
                try {
                    $newSeeders[] = $this->seedSeeder($inputOutput, $seeder);
                } catch (\Exception $e) {
                    $inputOutput->warning($e->getMessage());
                }
            }
        }

        if ($newSeeders) {
            $inputOutput->newLine();
            $result = $this->seederRepository->insertMany($newSeeders);
            $inputOutput->success($result->getInsertedCount() . ' seeders are seeded.');

            foreach ($newSeeders as $seeder) {
                $inputOutput->text('- Seeded ' . $seeder->getClass());
            }

            $inputOutput->newLine();
            return Command::SUCCESS;
        }

        $inputOutput->success('Nothing to seed! Everything seems updated.');

        return Command::SUCCESS;
    }

    /**
     * @param SymfonyStyle $inputOutput
     * @param SeederInterface $seeder
     * @return SeederDocumentInterface
     * @throws \Exception
     */
    protected function seedSeeder(
        SymfonyStyle $inputOutput,
        SeederInterface $seeder
    ): SeederDocumentInterface {
        $class = get_class($seeder);
        $classNameArray = explode('\\', $class);
        $inputOutput->text('- Running seeder ' . $class);

        try {
            $seederStatus = $seeder->seed();
        } catch (\Exception $e) {
            $inputOutput->error($e->getMessage());
            $inputOutput->text('- Rolling back seeder ' . $class);
            try {
                $seeder->rollback();
            } catch (\Exception $e) {
                $inputOutput->error($e->getMessage());
            }
            $seederStatus = false;
        }

        if (!$seederStatus) {
            throw new \Exception('Seeder failed for ' . $classNameArray[count($classNameArray) - 1]);
        }

        /** @var SeederDocumentInterface $seederDocument */
        $seederDocument = $this->documentFactory->create(SeederDocument::class, [
            'class' => $class,
        ]);

        $bundleNameArray = [];
        foreach ($classNameArray as $str) {
            if ('Seeder' === $str) {
                break;
            }
            $bundleNameArray[] = ucfirst($str);
        }

        return $seederDocument
            ->setBundleName(implode('_', $bundleNameArray))
            ->setFileName($classNameArray[count($classNameArray) - 1])
            ->setSeededAt($this->systemDateTime->now());
    }
}
