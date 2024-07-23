<?php

namespace LaravelTool\SwooleRedisDriver;

use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        Redis::extend('swoole_redis', function () {
            return new SwooleRedisConnector();
        });
    }
}
