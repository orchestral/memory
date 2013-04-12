<?php namespace Orchestra\Memory\Tests;

class EnvironmentTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		$appMock = \Mockery::mock('Application')
			->shouldReceive('instance')->andReturn(true);

		\Illuminate\Support\Facades\Config::setFacadeApplication($appMock->getMock());
		\Illuminate\Support\Facades\DB::setFacadeApplication($appMock->getMock());
		
		$cacheMock = \Mockery::mock('Cache')
			->shouldReceive('get')->andReturn(array())
			->shouldReceive('forever')->andReturn(true);

		\Illuminate\Support\Facades\Cache::setFacadeApplication($appMock->getMock());
		\Illuminate\Support\Facades\Cache::swap($cacheMock->getMock());
	}

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		\Mockery::close();
	}

	/**
	 * Test that Orchestra\Memory\Environment::make() return an instanceof Orchestra\Memory\Environment.
	 * 
	 * @test
	 */
	public function testMake()
	{
		$configMock = \Mockery::mock('Config')
			->shouldReceive('get')
				->with('orchestra/memory::default_table')
				->once()
				->andReturn('options')
			->shouldReceive('get')
				->with('orchestra/memory::default_model')
				->once()
				->andReturn('options');

		\Illuminate\Support\Facades\Config::swap($configMock->getMock());

		$eloquentMock = \Mockery::mock('EloquentModelMock')
			->shouldReceive('all')->andReturn(array());
		$queryMock = \Mockery::mock('DB\Query')
			->shouldReceive('get')->andReturn(array());
		$dbMock = \Mockery::mock('DB')
			->shouldReceive('table')->andReturn($queryMock->getMock());

		\Illuminate\Support\Facades\DB::swap($dbMock->getMock());

		$stub = new \Orchestra\Memory\Environment;

		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Runtime', 
			$stub->make('runtime')); 
		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Cache', 
			$stub->make('cache')); 
		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Eloquent', 
			$stub->make('eloquent', array('name' => $eloquentMock->getMock()))); 
		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Fluent', 
			$stub->make('fluent')); 
	}

	/**
	 * Test that Orchestra\Memory\Environment::make() return exception when given invalid driver
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testMakeExpectedException()
	{
		with(new \Orchestra\Memory\Environment)->make('orm');
	}

	/**
	 * Test Orchestra\Memory\Environment::extend() return valid Memory instance.
	 *
	 * @test
	 */
	public function testStubMemory()
	{
		$stub = new \Orchestra\Memory\Environment;

		$stub->extend('stub', function($driver, $config) 
		{
			return new MemoryStub($driver, $config);
		});

		$stub = $stub->make('stub.mock');

		$this->assertInstanceOf('\Orchestra\Memory\Tests\MemoryStub', $stub);

		$refl    = new \ReflectionObject($stub);
		$storage = $refl->getProperty('storage');
		$storage->setAccessible(true);

		$this->assertEquals('stub', $storage->getValue($stub));
	}

	/**
	 * Test Orchestra\Memory\Environment::shutdown() method.
	 *
	 * @test
	 */
	public function testShutdownMethod()
	{
		$stub = new \Orchestra\Memory\Environment;
		$foo  = $stub->make('runtime.fool');

		$this->assertTrue($foo === $stub->make('runtime.fool'));

		$stub->shutdown();

		$this->assertFalse($foo === $stub->make('runtime.fool'));
	}
}

class MemoryStub extends \Orchestra\Memory\Drivers\Driver
{
	/**
	 * Storage name
	 * 
	 * @access  protected
	 * @var     string  
	 */
	protected $storage = 'stub';

	/**
	 * No initialize method for runtime
	 *
	 * @access  public
	 * @return  void
	 */
	public function initiate() {}

	/**
	 * No shutdown method for runtime
	 *
	 * @access  public
	 * @return  void
	 */
	public function shutdown() {}
}

class EloquentModelMock {

	public function all() {}

	public function where($key, $condition, $value) {}
}