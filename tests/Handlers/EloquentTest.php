<?php namespace Orchestra\Memory\Handlers\TestCase;

use Mockery as m;
use Illuminate\Support\Fluent;
use Orchestra\Memory\Handlers\Eloquent;

class EloquentTest extends \PHPUnit_Framework_TestCase
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
    protected function eloquentDataProvider()
    {
        return array(
            new Fluent(array('id' => 1, 'name' => 'foo', 'value' => 's:6:"foobar";')),
            new Fluent(array('id' => 2, 'name' => 'hello', 'value' => 's:5:"world";')),
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
        $cache    = m::mock('\Illuminate\Cache\Repository');
        $eloquent = m::mock('EloquentHandlerModelMock');

        $config = array('model' => 'EloquentHandlerModelMock', 'cache' => true);
        $data   = $this->eloquentDataProvider();

        $app->shouldReceive('make')->once()->with('EloquentHandlerModelMock')->andReturn($eloquent);
        $cache->shouldReceive('get')->once()
                ->with('db-memory:eloquent-stub', m::type('Closure'))
                ->andReturnUsing(function ($n, $c) {
                    return $c();
                })
            ->shouldReceive('put')->once()->with('db-memory:eloquent-stub', $data, 60)->andReturnNull();
        $eloquent->shouldReceive('newInstance')->once()->andReturn($eloquent)
            ->shouldReceive('get')->andReturn($data);

        $stub = new Eloquent('stub', $config, $app, $cache);

        $expected = array(
            'foo'   => 'foobar',
            'hello' => 'world',
        );

        $this->assertInstanceOf('\Orchestra\Memory\Handlers\Eloquent', $stub);
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
        $cache    = m::mock('\Illuminate\Cache\Repository');
        $eloquent = m::mock('EloquentHandlerModelMock');

        $config = array('model' => $eloquent, 'cache' => true);
        $data   = $this->eloquentDataProvider();

        $checkWithCountQuery    = m::mock('\Illuminate\Database\Query\Builder');
        $checkWithoutCountQuery = m::mock('\Illuminate\Database\Query\Builder');
        $fooEntity              = m::mock('FooEntityMock');

        $app->shouldReceive('make')->times(4)->with('EloquentHandlerModelMock')->andReturn($eloquent);
        $cache->shouldReceive('get')->once()
            ->with('db-memory:eloquent-stub', m::type('Closure'))
            ->andReturnUsing(function ($n, $c) {
                    return $c();
                })
            ->shouldReceive('put')->once()->with('db-memory:eloquent-stub', $data, 60)->andReturnNull()
            ->shouldReceive('forget')->once()->with('db-memory:eloquent-stub')->andReturn(null);
        $eloquent->shouldReceive('newInstance')->times(4)->andReturn($eloquent)
            ->shouldReceive('get')->once()->andReturn($data)
            ->shouldReceive('create')->once()->andReturn(true)
            ->shouldReceive('where')->with('name', '=', 'foo')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'hello')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'stubbed')->andReturn($checkWithoutCountQuery);
        $checkWithCountQuery->shouldReceive('first')->andReturn($fooEntity);
        $checkWithoutCountQuery->shouldReceive('first')->andReturnNull();
        $fooEntity->shouldReceive('save')->once()->andReturn(true);

        $stub = new Eloquent('stub', $config, $app, $cache);
        $stub->initiate();

        $items = array(
            'foo' => 'foobar is wicked',
            'hello' => 'world',
            'stubbed' => 'Foobar was awesome',
        );

        $this->assertTrue($stub->finish($items));
    }
}
