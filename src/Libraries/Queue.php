<?php

namespace Masrodjie\Queue\Libraries;

use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Capsule\Manager as QueueManager;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Redis\RedisManager;

class Queue
{
    public $queue;

    public function __construct()
    {
        $queue = new QueueManager;
        $container = $queue->getContainer();
        $container['config'] = [
            'queue.connections.redis' => [
                'driver' => 'redis',
                'connection' => 'default',
                'queue' => getenv('REDIS_PREFIX') != '' ? getenv('REDIS_PREFIX')  : $_ENV['REDIS_PREFIX'],
                'retry_after' => 30,
            ],
            'queue.default' => 'redis',
            'cache.default' => 'redis',
            'cache.stores.redis' => [
                'driver' => 'redis',
                'connection' => 'default'
            ],
            'cache.prefix' => 'illuminate_non_laravel',
            'database.redis' => [
                'cluster' => false,
                'default' => [
                    'scheme' => (getenv('REDIS_SCHEME') != '' ? getenv('REDIS_SCHEME')  : $_ENV['REDIS_SCHEME']),
                    'host' => (getenv('REDIS_HOST') != '' ? getenv('REDIS_HOST')  : $_ENV['REDIS_HOST']),
                    'port' => (getenv('REDIS_PORT') != '' ? getenv('REDIS_PORT')  : $_ENV['REDIS_PORT']),
                    'database' => (getenv('REDIS_DB') != '' ? getenv('REDIS_DB')  : $_ENV['REDIS_DB']),
                    'password' => (getenv('REDIS_PASSWORD') != '' ? getenv('REDIS_PASSWORD')  : $_ENV['REDIS_PASSWORD']),
                    'username' => (getenv('REDIS_USERNAME') != '' ? getenv('REDIS_USERNAME')  : $_ENV['REDIS_USERNAME']),
                ],
            ]
        ];

        $container['redis'] = new RedisManager(new \Masrodjie\Queue\Containers\Application, 'predis', $container['config']['database.redis']);

        $queue->setAsGlobal();
        $this->queue = $queue;

    }

    public function __call($method, $parameters)
    {
        return $this->queue->$method(...$parameters);
    }
}
