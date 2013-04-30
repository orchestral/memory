<?php namespace Orchestra\Memory\Tests;

use Mockery as m;
use Orchestra\Memory\MemoryManager;

class MemoryManagerTest extends \PHPUnit_Framework_TestCase {

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
		$this->app = array(
			'cache' => $cache = m::mock('Cache'),
		);
		
		$cache->shouldReceive('get')->andReturn(array())
			->shouldReceive('forever')->andReturn(true);
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
	 * Test that Orchestra\Memory\MemoryManager::make() return an instanceof 
	 * Orchestra\Memory\MemoryManager.
	 * 
	 * @test
	 */
	public function testMakeMethod()
	{
		$app           = $this->app;
		$app['config'] = $config = m::mock('Config');
		$app['db']     = $db = m::mock('DB');
		
		$eloquent = m::mock('EloquentModelMock');
		$query    = m::mock('DB\Query');

		$config->shouldReceive('get')->with('orchestra/memory::cache.default', array())->once()->andReturn(array())
			->shouldReceive('get')->with('orchestra/memory::fluent.default', array())->once()->andReturn(array('table' => 'orchestra_options'))
			->shouldReceive('get')->with('orchestra/memory::eloquent.default', array())->once()->andReturn(array('model' => $eloquent))
			->shouldReceive('get')->with('orchestra/memory::runtime.default', array())->once()->andReturn(array());
		$eloquent->shouldReceive('all')->andReturn(array());
		$db->shouldReceive('table')->andReturn($query);
		$query->shouldReceive('get')->andReturn(array());

		$stub = new MemoryManager($app);

		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Runtime', $stub->make('runtime')); 
		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Cache', $stub->make('cache')); 
		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Eloquent', $stub->make('eloquent')); 
		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Fluent', $stub->make('fluent')); 
	}

	/**
	 * Test that Orchestra\Memory\MemoryManager::make() return exception when given invalid driver
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testMakeExpectedException()
	{
		with(new MemoryManager($this->app))->make('orm');
	}

	/**
	 * Test Orchestra\Memory\MemoryManager::extend() return valid Memory instance.
	 *
	 * @test
	 */
	public function testStubMemory()
	{
		$app           = $this->app;
		$app['config'] = $config = m::mock('Config');
		
		$config->shouldReceive('get')->with('orchestra/memory::stub.mock', array())->once()->andReturn(array());

		$stub = new MemoryManager($app);

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
		$app           = $this->app;
		$app['config'] = $config = m::mock('Config');
		
		$config->shouldReceive('get')->with('orchestra/memory::runtime.fool', array())->twice()->andReturn(array());

		$stub = new MemoryManager($app);
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
		$app           = $this->app;
		$app['config'] = $config = m::mock('Config');
		
		$config->shouldReceive('get')->with('orchestra/memory::runtime.default', array())->once()->andReturn(array())
			->shouldReceive('get')->with('orchestra/memory::config.driver', 'fluent.default')
				->once()->andReturn('runtime.default');

		$stub = new MemoryManager($app);
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
