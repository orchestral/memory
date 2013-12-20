<?php namespace Orchestra\Memory\TestCase;

use Mockery as m;
use Orchestra\Memory\FluentMemoryHandler;

class FluentTest extends \PHPUnit_Framework_TestCase
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
    public static function providerFluent()
    {
        return array(
            new \Illuminate\Support\Fluent(array('id' => 1, 'name' => 'foo', 'value' => 's:6:"foobar";')),
            new \Illuminate\Support\Fluent(array('id' => 2, 'name' => 'hello', 'value' => 's:5:"world";')),
        );
    }

    /**
     * Test Orchestra\Memory\FluentMemoryHandler::initiate() method.
     *
     * @test
     */
    public function testInitiateMethod()
    {
        $cache = m::mock('\Illuminate\Cache\Repository');
        $db = m::mock('\Illuminate\Database\DatabaseManager');

        $config = array('table' => 'orchestra_options');
        $items  = static::providerFluent();

        $query = m::mock('DB\Query');

        $db->shouldReceive('table')->once()->andReturn($query);
        $query->shouldReceive('remember')->once()->with(60, 'db-memory:fluent-stub')->andReturn($query)
            ->shouldReceive('get')->andReturn($items);

        $stub = new FluentMemoryHandler('stub', $config, $db, $cache);

        $expected = array(
            'foo'   => 'foobar',
            'hello' => 'world',
        );

        $this->assertInstanceOf('\Orchestra\Memory\FluentMemoryHandler', $stub);
        $this->assertEquals($expected, $stub->initiate());
    }

    /**
     * Test Orchestra\Memory\FluentMemoryHandler::finish() method.
     *
     * @test
     * @group support
     */
    public function testFinishMethod()
    {
        $cache = m::mock('\Illuminate\Cache\Repository');
        $db = m::mock('\Illuminate\Database\DatabaseManager');

        $config = array('table' => 'orchestra_options');

        $selectQuery            = m::mock('DB\Query');
        $checkWithCountQuery    = m::mock('DB\Query');
        $checkWithoutCountQuery = m::mock('DB\Query');

        $cache->shouldReceive('forget')->once()->with('db-memory:fluent-stub')->andReturn(null);
        $checkWithCountQuery->shouldReceive('count')->andReturn(1);
        $checkWithoutCountQuery->shouldReceive('count')->andReturn(0);
        $selectQuery->shouldReceive('update')->with(array('value' => serialize('foobar is wicked')))->once()->andReturn(true)
            ->shouldReceive('insert')->once()->andReturn(true)
            ->shouldReceive('where')->with('name', '=', 'foo')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'hello')->andReturn($checkWithCountQuery)
            ->shouldReceive('where')->with('name', '=', 'stubbed')->andReturn($checkWithoutCountQuery)
            ->shouldReceive('get')->andReturn(static::providerFluent())
            ->shouldReceive('where')->with('id', '=', 1)->andReturn($selectQuery)
            ->shouldReceive('remember')->once()->with(60, 'db-memory:fluent-stub')->andReturn($selectQuery);
        $db->shouldReceive('table')->times(5)->andReturn($selectQuery);

        $stub = new FluentMemoryHandler('stub', $config, $db, $cache);
        $stub->initiate();

        $items = array(
            'foo' => 'foobar is wicked',
            'hello' => 'world',
            'stubbed' => 'Foobar was awesome',
        );

        $this->assertTrue($stub->finish($items));
    }
}
