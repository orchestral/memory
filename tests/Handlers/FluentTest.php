<?php

namespace Orchestra\Memory\Handlers\TestCase;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Collection;
use Orchestra\Memory\Handlers\Fluent;

class FluentTest extends TestCase
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
    protected function fluentDataProvider()
    {
        return new Collection([
            new \Illuminate\Support\Fluent(['id' => 1, 'name' => 'foo', 'value' => 's:6:"foobar";']),
            new \Illuminate\Support\Fluent(['id' => 2, 'name' => 'hello', 'value' => 's:5:"world";']),
        ]);
    }

    /**
     * Test Orchestra\Memory\Handlers\Fluent::initiate() method.
     *
     * @test
     */
    public function testInitiateMethod()
    {
        $cache = m::mock('\Illuminate\Contracts\Cache\Repository');
        $db    = m::mock('\Illuminate\Database\DatabaseManager');

        $config = ['table' => 'orchestra_options', 'cache' => true];
        $data   = $this->fluentDataProvider();

        $query = m::mock('\Illuminate\Database\Query\Builder');

        $db->shouldReceive('table')->once()->andReturn($query);
        $cache->shouldReceive('rememberForever')->once()
            ->with('db-memory:fluent-stub', m::type('Closure'))
            ->andReturnUsing(function ($n, $c) {
                return $c();
            });
        $query->shouldReceive('get')->andReturn($data);

        $stub = new Fluent('stub', $config, $db, $cache);

        $expected = [
            'foo'   => 'foobar',
            'hello' => 'world',
        ];

        $this->assertInstanceOf('\Orchestra\Memory\Handlers\Fluent', $stub);
        $this->assertEquals($expected, $stub->initiate());
    }

    /**
     * Test Orchestra\Memory\Handlers\Fluent::finish() method.
     *
     * @test
     * @group support
     */
    public function testFinishMethod()
    {
        $cache = m::mock('\Illuminate\Contracts\Cache\Repository');
        $db    = m::mock('\Illuminate\Database\DatabaseManager');

        $config = ['table' => 'orchestra_options', 'cache' => true];
        $data   = $this->fluentDataProvider();

        $selectQuery            = m::mock('\Illuminate\Database\Query\Builder');
        $checkWithCountQuery    = m::mock('\Illuminate\Database\Query\Builder');
        $checkWithoutCountQuery = m::mock('\Illuminate\Database\Query\Builder');

        $cache->shouldReceive('rememberForever')->once()
            ->with('db-memory:fluent-stub', m::type('Closure'))
            ->andReturnUsing(function ($n, $c) {
                    return $c();
                })
            ->shouldReceive('forget')->once()->with('db-memory:fluent-stub')->andReturn(null);
        $checkWithCountQuery->shouldReceive('count')->andReturn(1);
        $checkWithoutCountQuery->shouldReceive('count')->andReturn(0);
        $selectQuery->shouldReceive('update')->with(['value' => serialize('foobar is wicked')])->once()->andReturn(true)
            ->shouldReceive('insert')->once()->andReturn(true)
            ->shouldReceive('where')->with('name', '=', 'foo')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'hello')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'stubbed')->andReturn($checkWithoutCountQuery)
            ->shouldReceive('get')->andReturn($data)
            ->shouldReceive('where')->with('id', '=', 1)->andReturn($selectQuery);
        $db->shouldReceive('table')->times(5)->andReturn($selectQuery);

        $stub = new Fluent('stub', $config, $db, $cache);
        $stub->initiate();

        $items = [
            'foo'     => 'foobar is wicked',
            'hello'   => 'world',
            'stubbed' => 'Foobar was awesome',
        ];

        $this->assertTrue($stub->finish($items));
    }
}
