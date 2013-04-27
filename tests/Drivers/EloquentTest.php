<?php namespace Orchestra\Memory\Tests\Drivers;

use Mockery as m;
use Orchestra\Memory\Drivers\Eloquent;

class EloquentTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Application mock instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	private $app = null;

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		$this->app = m::mock('\Illuminate\Foundation\Application');
		$this->app->shouldReceive('instance')->andReturn(true);

		\Illuminate\Support\Facades\Config::setFacadeApplication($this->app);
	}

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		unset($this->app);
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
		$config   = m::mock('Config');
		$eloquent = m::mock('EloquentModelMock');

		\Illuminate\Support\Facades\Config::swap($config);
		
		$config->shouldReceive('get')
			->with('orchestra/memory::eloquent.stub', array())
			->once()->andReturn(array('model' => $eloquent));
		$eloquent->shouldReceive('all')->andReturn(static::providerEloquent());

		$stub = new Eloquent($this->app, 'stub');

		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Eloquent', $stub);
		$this->assertEquals('foobar', $stub->get('foo'));
		$this->assertEquals('world', $stub->get('hello'));
	}

	/**
	 * Test Orchestra\Memory\Drivers\Eloquent::shutdown() method.
	 *
	 * @test
	 */
	public function testShutdownMethod()
	{
		$config                 = m::mock('Config');
		$eloquent               = m::mock('EloquentModelMock');
		$checkWithCountQuery    = m::mock('DB\Query');
		$checkWithoutCountQuery = m::mock('DB\Query');
		$fooEntity              = m::mock('FooEntityMock');

		\Illuminate\Support\Facades\Config::swap($config);
		
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
		$fooEntity->shouldReceive('fill')->once()->andReturn(true)
			->shouldReceive('save')->once()->andReturn(true);
		
		$stub = new Eloquent($this->app, 'stub');

		$stub->put('foo', 'foobar is wicked');
		$stub->put('stubbed', 'Foobar was awesome');

		$stub->shutdown();
	}
}

class EloquentModelMock {

	public function all() {}

	public function where($key, $condition, $value) {}
}
