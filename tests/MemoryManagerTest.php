<?php namespace Orchestra\Memory\Tests;

class MemoryManagerTest extends \PHPUnit_Framework_TestCase {

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

		\Illuminate\Support\Facades\Cache::setFacadeApplication($this->app);
		\Illuminate\Support\Facades\Config::setFacadeApplication($this->app);
		\Illuminate\Support\Facades\DB::setFacadeApplication($this->app);
		
		$cacheMock = \Mockery::mock('Cache')
			->shouldReceive('get')->andReturn(array())
			->shouldReceive('forever')->andReturn(true);

		\Illuminate\Support\Facades\Cache::swap($cacheMock->getMock());
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
	 * Test that Orchestra\Memory\MemoryManager::make() return an instanceof 
	 * Orchestra\Memory\MemoryManager.
	 * 
	 * @test
	 */
	public function testMakeMethod()
	{
		\Illuminate\Support\Facades\Config::swap($configMock = \Mockery::mock('Config'));
		\Illuminate\Support\Facades\DB::swap($dbMock = \Mockery::mock('DB'));

		$configMock->shouldReceive('get')
				->with('orchestra/memory::cache.default', array())
				->once()
				->andReturn(array())
			->shouldReceive('get')
				->with('orchestra/memory::fluent.default', array())
				->once()
				->andReturn(array('table' => 'orchestra_options'))
			->shouldReceive('get')
				->with('orchestra/memory::eloquent.default', array())
				->once()
				->andReturn(array('model' => $eloquentMock = \Mockery::mock('EloquentModelMock')))
			->shouldReceive('get')
				->with('orchestra/memory::runtime.default', array())
				->once()
				->andReturn(array());

		$eloquentMock->shouldReceive('all')->andReturn(array());
		
		$dbMock->shouldReceive('table')->andReturn($queryMock = \Mockery::mock('DB\Query'));
		$queryMock->shouldReceive('get')->andReturn(array());

		$stub = new \Orchestra\Memory\MemoryManager($this->app);

		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Runtime', 
			$stub->make('runtime')); 
		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Cache', 
			$stub->make('cache')); 
		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Eloquent', 
			$stub->make('eloquent')); 
		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Fluent', 
			$stub->make('fluent')); 
	}

	/**
	 * Test that Orchestra\Memory\MemoryManager::make() return exception when given invalid driver
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testMakeExpectedException()
	{
		with(new \Orchestra\Memory\MemoryManager($this->app))->make('orm');
	}

	/**
	 * Test Orchestra\Memory\MemoryManager::extend() return valid Memory instance.
	 *
	 * @test
	 */
	public function testStubMemory()
	{
		\Illuminate\Support\Facades\Config::swap($configMock = \Mockery::mock('Config'));
		
		$configMock->shouldReceive('get')
				->with('orchestra/memory::stub.mock', array())
				->once()
				->andReturn(array());

		$stub = new \Orchestra\Memory\MemoryManager($this->app);

		$stub->extend('stub', function($app, $name) 
		{
			return new MemoryStub($app, $name);
		});

		$stub = $stub->make('stub.mock');

		$this->assertInstanceOf('\Orchestra\Memory\Tests\MemoryStub', $stub);

		$refl    = new \ReflectionObject($stub);
		$app     = $refl->getProperty('app');
		$name    = $refl->getProperty('name');
		$storage = $refl->getProperty('storage');

		$app->setAccessible(true);
		$name->setAccessible(true);
		$storage->setAccessible(true);

		$this->assertInstanceOf('\Illuminate\Foundation\Application', $app->getValue($stub));
		$this->assertEquals('mock', $name->getValue($stub));
		$this->assertEquals('stub', $storage->getValue($stub));
	}

	/**
	 * Test Orchestra\Memory\MemoryManager::shutdown() method.
	 *
	 * @test
	 */
	public function testShutdownMethod()
	{
		\Illuminate\Support\Facades\Config::swap($configMock = \Mockery::mock('Config'));
		
		$configMock->shouldReceive('get')
				->with('orchestra/memory::runtime.fool', array())
				->twice()
				->andReturn(array());

		$stub = new \Orchestra\Memory\MemoryManager($this->app);
		$foo  = $stub->make('runtime.fool');

		$this->assertTrue($foo === $stub->make('runtime.fool'));

		$stub->shutdown();

		$this->assertFalse($foo === $stub->make('runtime.fool'));
	}

	/**
	 * Test that Orchestra\Memory\MemoryManager::make() default driver.
	 * 
	 * @test
	 */
	public function testMakeMethodForDefaultDriver()
	{
		\Illuminate\Support\Facades\Config::swap($config = \Mockery::mock('Config'));
		
		$config->shouldReceive('get')
				->with('orchestra/memory::runtime.default', array())
				->once()
				->andReturn(array());

		$app = array(
			'config' => ($appConfig = \Mockery::mock('Config')),
		);
		
		$appConfig->shouldReceive('get')
				->with('orchestra/memory::config.driver')
				->once()
				->andReturn('runtime.default');

		$stub = new \Orchestra\Memory\MemoryManager($app);
		$stub->make();
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