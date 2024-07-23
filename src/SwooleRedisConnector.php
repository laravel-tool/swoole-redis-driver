<?php

namespace LaravelTool\SwooleRedisDriver;

use Illuminate\Contracts\Redis\Connector;
use Illuminate\Redis\Connections\Connection;
use Swoole\ConnectionPool;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

class SwooleRedisConnector implements Connector
{

    public function connect(array $config, array $options): Connection
    {
        $redisConfig = (new RedisConfig())
            ->withHost($config['host'])
            ->withDbIndex($config['database'])
            ->withPort($config['port']);

        if (!empty($config['username'])) {
            $redisConfig->withAuth(sprintf('%s:%s', $config['username'], $config['password']));
        }

        $poolSize = $config['pool_size'] ?? ConnectionPool::DEFAULT_SIZE;

        return new SwooleRedisConnection(new RedisPool($redisConfig, $poolSize));
    }

    public function connectToCluster(array $config, array $clusterOptions, array $options)
    {
        // TODO: Implement connectToCluster() method.
    }
}
