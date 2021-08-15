<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

namespace EveryWorkflow\MongoBundle\Tests\Unit;

use EveryWorkflow\MongoBundle\Repository\BaseRepository;
use EveryWorkflow\MongoBundle\Tests\BaseMongoTestCase;

class MongoConnectionTest extends BaseMongoTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        $mongoConnection = $this->getMongoConnection();
        $baseRepository = new BaseRepository($mongoConnection);
        $baseRepository->setCollectionName('test_document_collection')
            ->getCollection()
            ->drop();
    }

    public function test_mongodb_test_connection(): void
    {
        $mongoConnection = $this->getMongoConnection();
        $baseRepository = new BaseRepository($mongoConnection);

        $userData = [
            [
                'first_name' => 'Test 1',
                'last_name' => 'Name 1',
                'email' => 'test1@example.com',
                'gender' => 'male',
            ],
            [
                'first_name' => 'Test 2',
                'last_name' => 'Name 2',
                'email' => 'test2@example.com',
                'gender' => 'male',
            ],
        ];
        $userRepository = $baseRepository->setCollectionName('test_document_collection');
        $userRepository->getCollection()->insertMany($userData);

        self::assertCount($userRepository->getCollection()->countDocuments(), $userData, 'Stored document count must be same.');

        /** @var \MongoDB\Model\BSONDocument $dbUser1 */
        $dbUser1 = $baseRepository->getCollection()->findOne(['email' => 'test1@example.com']);
        $dbUser1Data = $dbUser1->getArrayCopy();
        self::assertArrayHasKey('_id', $dbUser1Data, 'Db user1 data must have >> _id << array key.');
        self::assertArrayHasKey('first_name', $dbUser1Data, 'Db user1 data must have >> first_name << array key.');
        self::assertArrayHasKey('last_name', $dbUser1Data, 'Db user1 data must have >> last_name << array key.');
        self::assertArrayHasKey('email', $dbUser1Data, 'Db user1 data must have >> email << array key.');
        self::assertEquals('test1@example.com', $dbUser1Data['email'], 'Db user1 email must be same.');
        self::assertArrayHasKey('gender', $dbUser1Data, 'Db user1 data must have >> gender << array key.');

        /** @var \MongoDB\Model\BSONDocument $dbUser2 */
        $dbUser2 = $baseRepository->getCollection()->findOne(['email' => 'test2@example.com']);
        $dbUser2Data = $dbUser2->getArrayCopy();
        self::assertArrayHasKey('_id', $dbUser2Data, 'Db user2 data must have >> _id << array key.');
        self::assertArrayHasKey('first_name', $dbUser2Data, 'Db user2 data must have >> first_name << array key.');
        self::assertArrayHasKey('last_name', $dbUser2Data, 'Db user2 data must have >> last_name << array key.');
        self::assertArrayHasKey('email', $dbUser2Data, 'Db user2 data must have >> email << array key.');
        self::assertEquals('test2@example.com', $dbUser2Data['email'], 'Db user2 email must be same.');
        self::assertArrayHasKey('gender', $dbUser2Data, 'Db user2 data must have >> gender << array key.');
    }
}
