<?php

namespace LaravelTool\SwooleRedisDriver;

use Closure;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Events\CommandExecuted;
use Redis;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Database\RedisPool;
use function Co\run;

/**
 * @method pubSubLoop()
 */
class SwooleRedisConnection extends Connection
{

    public function __construct(
        public RedisPool $redisPool
    ) {
        $this->client = null;
    }

    public function createSubscription($channels, Closure $callback, $method = 'subscribe')
    {
        $loop = $this->pubSubLoop();

        $loop->{$method}(...array_values((array) $channels));

        foreach ($loop as $message) {
            if ($message->kind === 'message' || $message->kind === 'pmessage') {
                $callback($message->payload, $message->channel);
            }
        }

        unset($loop);
    }

    public function client()
    {
        return $this->redisPool->get();
    }

    public function command($method, array $parameters = [])
    {
        $channel = new Channel(1);
        go(function () use ($method, $parameters, $channel) {
            $start = microtime(true);

            $redis = $this->redisPool->get();
            $result = $redis->{$method}(...$parameters);
            $this->redisPool->put($redis);

            $time = round((microtime(true) - $start) * 1000, 2);

            if (isset($this->events)) {
                $this->event(new CommandExecuted(
                    $method, $this->parseParametersForEvent($parameters), $time, $this
                ));
            }

            $channel->push($result);
        });

        return $channel->pop();
    }
}
