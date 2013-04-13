<?php namespace Orchestra\Memory\Tests\Drivers;

class EloquentTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Application mock instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app = null;

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		$this->app = \Mockery::mock('\Illuminate\Foundation\Application');
		$this->app->shouldReceive('instance')
				->andReturn(true);

		\Illuminate\Support\Facades\Config::setFacadeApplication($this->app);
	}

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		unset($this->app);
		\Mockery::close();
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
	 * @group support
	 */
	public function testInitiateMethod()
	{
		\Illuminate\Support\Facades\Config::swap($configMock = \Mockery::mock('Config'));
		$configMock->shouldReceive('get')
			->with('orchestra/memory::eloquent.stub', array())
			->once()
			->andReturn(array('model' => $eloquentMock = \Mockery::mock('EloquentModelMock')));

		$eloquentMock->shouldReceive('all')->andReturn(static::providerEloquent());

		$stub = new \Orchestra\Memory\Drivers\Eloquent($this->app, 'stub');

		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Eloquent', $stub);
		$this->assertEquals('foobar', $stub->get('foo'));
		$this->assertEquals('world', $stub->get('hello'));
	}

	/**
	 * Test Orchestra\Memory\Drivers\Eloquent::shutdown() method.
	 *
	 * @test
	 * @group support
	 */
	public function testShutdownMethod()
	{
		\Illuminate\Support\Facades\Config::swap($configMock = \Mockery::mock('Config'));
		$configMock->shouldReceive('get')
			->with('orchestra/memory::eloquent.stub', array())
			->once()
			->andReturn(array('model' => $eloquentMock = \Mockery::mock('EloquentModelMock')));

		$eloquentMock->shouldReceive('all')
				->andReturn(static::providerEloquent())
			->shouldReceive('create')
				->once()->andReturn(true)
			->shouldReceive('where')
				->with('name', '=', 'foo')->andReturn($checkWithCountQueryMock = \Mockery::mock('DB\Query'))
			->shouldReceive('where')
				->with('name', '=', 'hello')->andReturn($checkWithCountQueryMock)
			->shouldReceive('where')
				->with('name', '=', 'stubbed')->andReturn($checkWithoutCountQueryMock = \Mockery::mock('DB\Query'));

		$checkWithCountQueryMock->shouldReceive('count')->andReturn(1)
			->shouldReceive('first')->andReturn($fooEntityMock = \Mockery::mock('FooEntityMock'));
		$checkWithoutCountQueryMock->shouldReceive('count')->andReturn(0);
		$fooEntityMock->shouldReceive('fill')->once()->andReturn(true)
			->shouldReceive('save')->once()->andReturn(true);
		
		$stub = new \Orchestra\Memory\Drivers\Eloquent($this->app, 'stub');

		$stub->put('foo', 'foobar is wicked');
		$stub->put('stubbed', 'Foobar was awesome');

		$stub->shutdown();
	}
}

class EloquentModelMock {

	public function all() {}

	public function where($key, $condition, $value) {}
}