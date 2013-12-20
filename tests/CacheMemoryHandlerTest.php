<?php namespace Orchestra\Memory\Drivers\TestCase;

use Mockery as m;
use Orchestra\Memory\CacheMemoryHandler;

class CacheMemoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Memory\CacheMemoryHandler::initiate() method.
     *
     * @test
     */
    public function testInitiateMethod()
    {
        $cache = m::mock('\Illuminate\Cache\Repository');

        $value = array(
            'name' => 'Orchestra',
            'theme' => array(
                'backend' => 'default',
                'frontend' => 'default',
            ),
        );

        $cache->shouldReceive('get')->once()->andReturn($value);

        $stub = new CacheMemoryHandler('cachemock', array(), $cache);

        $this->assertEquals($value, $stub->initiate());
    }

    /**
     * Test Orchestra\Memory\CacheMemoryHandler::finish()
     *
     * @test
     */
    public function testFinishMethod()
    {
        $cache = m::mock('\Illuminate\Cache\Repository');

        $cache->shouldReceive('forever')->once()->andReturn(true);

        $stub = new CacheMemoryHandler('cachemock', array(), $cache);

        $this->assertTrue($stub->finish());
    }
}
