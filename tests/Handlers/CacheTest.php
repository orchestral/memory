<?php

namespace Orchestra\Memory\Handlers\TestCase;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Orchestra\Memory\Handlers\Cache;

class CacheTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Memory\Handlers\Cache::initiate() method.
     *
     * @test
     */
    public function testInitiateMethod()
    {
        $cache = m::mock('\Illuminate\Contracts\Cache\Repository');

        $value = [
            'name' => 'Orchestra',
            'theme' => [
                'backend' => 'default',
                'frontend' => 'default',
            ],
        ];

        $cache->shouldReceive('get')->once()->andReturn($value);

        $stub = new Cache('cachemock', [], $cache);

        $this->assertEquals($value, $stub->initiate());
    }

    /**
     * Test Orchestra\Memory\Handlers\Cache::finish().
     *
     * @test
     */
    public function testFinishMethod()
    {
        $cache = m::mock('\Illuminate\Contracts\Cache\Repository');

        $cache->shouldReceive('forever')->once()->andReturn(true);

        $stub = new Cache('cachemock', [], $cache);

        $this->assertTrue($stub->finish());
    }
}
