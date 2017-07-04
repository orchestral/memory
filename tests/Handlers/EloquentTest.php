<?php

namespace Orchestra\Memory\Handlers\TestCase;

use Mockery as m;
use Illuminate\Support\Fluent;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Collection;
use Orchestra\Memory\Handlers\Eloquent;

class EloquentTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Add data provider.
     *
     * @return array
     */
    protected function eloquentDataProvider()
    {
        return new Collection([
            new Fluent(['id' => 1, 'name' => 'foo', 'value' => 's:6:"foobar";']),
            new Fluent(['id' => 2, 'name' => 'hello', 'value' => 's:5:"world";']),
        ]);
    }

    /**
     * Test Orchestra\Memory\EloquentMemoryHandler::initiate() method.
     *
     * @test
     */
    public function testInitiateMethod()
    {
        $app      = m::mock('\Illuminate\Container\Container');
        $cache    = m::mock('\Illuminate\Contracts\Cache\Repository');
        $eloquent = m::mock('EloquentHandlerModelMock');

        $config = ['model' => 'EloquentHandlerModelMock', 'cache' => true];
        $data   = $this->eloquentDataProvider();

        $app->shouldReceive('make')->once()->with('EloquentHandlerModelMock')->andReturn($eloquent);
        $cache->shouldReceive('rememberForever')->once()
                ->with('db-memory:eloquent-stub', m::type('Closure'))
                ->andReturnUsing(function ($n, $c) {
                    return $c();
                });
        $eloquent->shouldReceive('newInstance')->once()->andReturn($eloquent)
            ->shouldReceive('get')->andReturn($data);

        $stub = new Eloquent('stub', $config, $app, $cache);

        $expected = [
            'foo'   => 'foobar',
            'hello' => 'world',
        ];

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
        $cache    = m::mock('\Illuminate\Contracts\Cache\Repository');
        $eloquent = m::mock('EloquentHandlerModelMock');

        $config = ['model' => $eloquent, 'cache' => true];
        $data   = $this->eloquentDataProvider();

        $checkWithCountQuery    = m::mock('\Illuminate\Database\Query\Builder');
        $checkWithoutCountQuery = m::mock('\Illuminate\Database\Query\Builder');
        $fooEntity              = m::mock('FooEntityMock');

        $app->shouldReceive('make')->times(4)->with('EloquentHandlerModelMock')->andReturn($eloquent);
        $cache->shouldReceive('rememberForever')->once()
            ->with('db-memory:eloquent-stub', m::type('Closure'))
            ->andReturnUsing(function ($n, $c) {
                    return $c();
                })
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

        $items = [
            'foo'     => 'foobar is wicked',
            'hello'   => 'world',
            'stubbed' => 'Foobar was awesome',
        ];

        $this->assertTrue($stub->finish($items));
    }
}
