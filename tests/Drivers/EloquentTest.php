<?php namespace Orchestra\Memory\Drivers\TestCase;

use Mockery as m;
use Orchestra\Memory\Drivers\Eloquent;

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
    public static function providerEloquent()
    {
        return array(
            new \Illuminate\Support\Fluent(array('id' => 1, 'name' => 'foo', 'value' => 's:6:"foobar";')),
            new \Illuminate\Support\Fluent(array('id' => 2, 'name' => 'hello', 'value' => 's:5:"world";')),
        );
    }

    /**
     * Test Orchestra\Memory\Drivers\Eloquent::initiate() method.
     *
     * @test
     */
    public function testInitiateMethod()
    {
        $app = m::mock('\Illuminate\Container\Container');
        $config = m::mock('Config');
        $eloquent = m::mock('EloquentModelMock');

        $app->shouldReceive('offsetGet')->once()->with('config')->andReturn($config);
        $app->shouldReceive('make')->once()->with('EloquentModelMock')->andReturn($eloquent);

        $config->shouldReceive('get')
            ->with('orchestra/memory::eloquent.stub', array())
            ->once()->andReturn(array('model' => $eloquent));
        $eloquent->shouldReceive('all')->andReturn(static::providerEloquent());

        $stub = new Eloquent($app, 'stub');

        $this->assertInstanceOf('\Orchestra\Memory\Drivers\Eloquent', $stub);
        $this->assertEquals('foobar', $stub->get('foo'));
        $this->assertEquals('world', $stub->get('hello'));
    }

    /**
     * Test Orchestra\Memory\Drivers\Eloquent::finish() method.
     *
     * @test
     */
    public function testFinishMethod()
    {
        $app = m::mock('\Illuminate\Container\Container');
        $config = m::mock('Config');

        $eloquent               = m::mock('EloquentModelMock');
        $checkWithCountQuery    = m::mock('DB\Query');
        $checkWithoutCountQuery = m::mock('DB\Query');
        $fooEntity              = m::mock('FooEntityMock');

        $app->shouldReceive('offsetGet')->once()->with('config')->andReturn($config);
        $app->shouldReceive('make')->once()->with('EloquentModelMock')->andReturn($eloquent);

        $config->shouldReceive('get')
            ->with('orchestra/memory::eloquent.stub', array())
            ->once()->andReturn(array('model' => $eloquent));

        $eloquent->shouldReceive('all')->andReturn(static::providerEloquent())
            ->shouldReceive('create')->once()->andReturn(true)
            ->shouldReceive('where')->with('name', '=', 'foo')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'hello')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'stubbed')->andReturn($checkWithoutCountQuery);
        $checkWithCountQuery->shouldReceive('count')->andReturn(1)
            ->shouldReceive('first')->andReturn($fooEntity);
        $checkWithoutCountQuery->shouldReceive('count')->andReturn(0);
        $fooEntity->shouldReceive('save')->once()->andReturn(true);

        $stub = new Eloquent($app, 'stub');

        $stub->put('foo', 'foobar is wicked');
        $stub->put('stubbed', 'Foobar was awesome');

        $stub->finish();
    }
}

class EloquentModelMock
{
    public function all()
    {
        //
    }

    public function where($key, $condition, $value)
    {
        //
    }
}
