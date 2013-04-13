<?php namespace Orchestra\Memory\Tests\Drivers;

class FluentTest extends \PHPUnit_Framework_TestCase {

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
		\Illuminate\Support\Facades\DB::setFacadeApplication($this->app);
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
	public static function providerFluent()
	{
		return array(
			new \Illuminate\Support\Fluent(array('id' => 1, 'name' => 'foo', 'value' => 's:6:"foobar";')),
			new \Illuminate\Support\Fluent(array('id' => 2, 'name' => 'hello', 'value' => 's:5:"world";')),
		);
	}

	/**
	 * Test Orchestra\Memory\Drivers\Fluent::initiate() method.
	 *
	 * @test
	 * @group support
	 */
	public function testInitiateMethod()
	{
		\Illuminate\Support\Facades\Config::swap($configMock = \Mockery::mock('Config'));
		\Illuminate\Support\Facades\DB::swap($dbMock = \Mockery::mock('DB'));

		$configMock->shouldReceive('get')
			->once()->with('orchestra/memory::fluent.stub', array())
			->andReturn(array('table' => 'orchestra_options'));

		$dbMock->shouldReceive('table')->andReturn($queryMock = \Mockery::mock('DB\Query'));
		$queryMock->shouldReceive('get')->andReturn(static::providerFluent());
			
		$stub = new \Orchestra\Memory\Drivers\Fluent($this->app, 'stub');

		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Fluent', $stub);
		$this->assertEquals('foobar', $stub->get('foo'));
		$this->assertEquals('world', $stub->get('hello'));
	}

	/**
	 * Test Orchestra\Memory\Drivers\Fluent::shutdown() method.
	 *
	 * @test
	 * @group support
	 */
	public function testShutdownMethod()
	{
		\Illuminate\Support\Facades\Config::swap($configMock = \Mockery::mock('Config'));
		\Illuminate\Support\Facades\DB::swap($dbMock = \Mockery::mock('DB'));

		$configMock->shouldReceive('get')
			->once()->with('orchestra/memory::fluent.stub', array())
			->andReturn(array('table' => 'orchestra_options'));

		$dbMock->shouldReceive('table')->andReturn($selectQueryMock = \Mockery::mock('DB\Query'));
		$selectQueryMock->shouldReceive('update')
				->with(array('value' => serialize('foobar is wicked')))
				->once()->andReturn(true)
			->shouldReceive('insert')
				->once()->andReturn(true)
			->shouldReceive('where')
				->with('name', '=', 'foo')->andReturn($checkWithCountQueryMock = \Mockery::mock('DB\Query'))
			->shouldReceive('where')
				->with('name', '=', 'hello')->andReturn($checkWithCountQueryMock)
			->shouldReceive('where')
				->with('name', '=', 'stubbed')->andReturn($checkWithoutCountQueryMock = \Mockery::mock('DB\Query'))
			->shouldReceive('get')
				->andReturn(static::providerFluent())
			->shouldReceive('where')
			->with('id', '=', 1)->andReturn($selectQueryMock);

		$checkWithCountQueryMock->shouldReceive('count')->andReturn(1);
		$checkWithoutCountQueryMock->shouldReceive('count')->andReturn(0);

		$stub = new \Orchestra\Memory\Drivers\Fluent($this->app, 'stub');

		$stub->put('foo', 'foobar is wicked');
		$stub->put('stubbed', 'Foobar was awesome');
		$stub->shutdown();
	}
}