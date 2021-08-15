<?php

/**
 * @copyright EveryWorkflow. All rights reserved.
 */

declare(strict_types=1);

namespace EveryWorkflow\MongoBundle\Model;

use MongoDB\Client;
use MongoDB\Database;

class MongoConnection implements MongoConnectionInterface
{
    /**
     * MongoDB Database Name.
     */
    protected string $mongoDb;
    /**
     * MongoDB Uri.
     */
    protected string $mongoUri;
    /**
     * MongoDB Uri Options.
     */
    protected array $mongoUriOptions = [];
    /**
     * MongoDB Driver Options.
     */
    protected array $mongoDriverOptions = [];
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var Database
     */
    protected $database;

    public function __construct(string $mongoUri, string $mongoDb)
    {
        $this->mongoUri = $mongoUri;
        $this->mongoDb = $mongoDb;
    }

    public function getClient(): Client
    {
        if ($this->client) {
            return $this->client;
        }
        $this->client = (new Client(
            $this->mongoUri,
            $this->mongoUriOptions,
            $this->mongoDriverOptions
        ));

        return $this->client;
    }

    public function getDatabase(): Database
    {
        if ($this->database) {
            return $this->database;
        }
        $this->database = $this->getClient()->selectDatabase($this->mongoDb);

        return $this->database;
    }
}
