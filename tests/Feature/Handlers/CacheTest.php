<?php

namespace Orchestra\Memory\Tests\Feature\Handlers;

use Mockery as m;
use Illuminate\Support\Facades\Cache;
use Orchestra\Support\Facades\Memory;
use Orchestra\Memory\Tests\Feature\TestCase;

class CacheTest extends TestCase
{
    /** @test */
    public function it_can_be_initiated()
    {
        $cache = m::mock('\Illuminate\Contracts\Cache\Repository');

        Cache::shouldReceive('driver')->with(null)->andReturn($cache);

        $value = [
            'name' => 'Orchestra',
            'theme' => [
                'backend' => 'default',
                'frontend' => 'default',
            ],
        ];

        $cache->shouldReceive('get')->once()->andReturn($value);

        $stub = Memory::make('cache.mock');

        $this->assertEquals($value, $stub->all());
    }

    /** @test */
    public function it_can_save_to_cache_on_close()
    {
        $cache = m::mock('\Illuminate\Contracts\Cache\Repository[forever]');
        $cache->shouldReceive('get')->once()->andReturn([])
            ->shouldReceive('forever')->once()->andReturn(true);

        Cache::shouldReceive('driver')->with(null)->andReturn($cache);

        $stub = Memory::make('cache.mock');

        $this->assertTrue($stub->finish());
    }
}
