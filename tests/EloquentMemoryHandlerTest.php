<?php namespace Orchestra\Memory\TestCase;

use Mockery as m;
use Orchestra\Memory\EloquentMemoryHandler;

class EloquentMemoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Add data provider
     *
     * @return array
     */
    public static function providerEloquent()
    {
        return array(
            new \Illuminate\Support\Fluent(array('id' => 1, 'name' => 'foo', 'value' => 's:6:"foobar";')),
            new \Illuminate\Support\Fluent(array('id' => 2, 'name' => 'hello', 'value' => 's:5:"world";')),
        );
    }

    /**
     * Test Orchestra\Memory\EloquentMemoryHandler::initiate() method.
     *
     * @test
     */
    public function testInitiateMethod()
    {
        $app      = m::mock('\Illuminate\Container\Container');
        $cache    = m::mock('\Illuminate\Cache\CacheManager');
        $eloquent = m::mock('EloquentHandlerModelMock');

        $config = array('model' => $eloquent, 'cache' => true);

        $app->shouldReceive('make')->once()->with('EloquentHandlerModelMock')->andReturn($eloquent);
        $eloquent->shouldReceive('newInstance')->once()->andReturn($eloquent)
            ->shouldReceive('remember')->once()->with(60, "db-memory:eloquent-stub")->andReturn($eloquent)
            ->shouldReceive('get')->andReturn(static::providerEloquent());

        $stub = new EloquentMemoryHandler('stub', $config, $app, $cache);

        $expected = array(
            'foo'   => 'foobar',
            'hello' => 'world',
        );

        $this->assertInstanceOf('\Orchestra\Memory\EloquentMemoryHandler', $stub);
        $this->assertEquals($expected, $stub->initiate());
    }

    /**
     * Test Orchestra\Memory\EloquentMemoryHandler::finish() method.
     *
     * @test
     */
    public function testFinishMethod()
    {
        $app      = m::mock('\Illuminate\Container\Container');
        $cache    = m::mock('\Illuminate\Cache\CacheManager');
        $eloquent = m::mock('EloquentHandlerModelMock');

        $config = array('model' => $eloquent, 'cache' => true);

        $checkWithCountQuery    = m::mock('DB\Query');
        $checkWithoutCountQuery = m::mock('DB\Query');
        $fooEntity              = m::mock('FooEntityMock');

        $app->shouldReceive('make')->times(4)->with('EloquentHandlerModelMock')->andReturn($eloquent);
        $cache->shouldReceive('forget')->once()->with('db-memory:eloquent-stub')->andReturn(null);
        $eloquent->shouldReceive('newInstance')->times(4)->andReturn($eloquent)
            ->shouldReceive('remember')->once()->with(60, "db-memory:eloquent-stub")->andReturn($eloquent)
            ->shouldReceive('get')->once()->andReturn(static::providerEloquent())
            ->shouldReceive('create')->once()->andReturn(true)
            ->shouldReceive('where')->with('name', '=', 'foo')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'hello')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'stubbed')->andReturn($checkWithoutCountQuery);
        $checkWithCountQuery->shouldReceive('first')->andReturn($fooEntity);
        $checkWithoutCountQuery->shouldReceive('first')->andReturnNull();
        $fooEntity->shouldReceive('save')->once()->andReturn(true);

        $stub = new EloquentMemoryHandler('stub', $config, $app, $cache);
        $stub->initiate();

        $items = array(
            'foo' => 'foobar is wicked',
            'hello' => 'world',
            'stubbed' => 'Foobar was awesome',
        );

        $this->assertTrue($stub->finish($items));
    }
}
