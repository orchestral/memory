<?php namespace Orchestra\Memory\Tests\Drivers;

class CacheTest extends \PHPUnit_Framework_TestCase {
	
	/**
	 * Application mock instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app = null;

	/**
	 * Stub instance.
	 *
	 * @var Orchestra\Memory\Drivers\Cache
	 */
	protected $stub = null;

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		$value = array(
			'name' => 'Orchestra',
			'theme' => array(
				'backend' => 'default',
				'frontend' => 'default',
			),
		);

		$this->app = \Mockery::mock('Illuminate\Foundation\Application');
		$this->app->shouldReceive('instance')
			->andReturn(true);

		\Illuminate\Support\Facades\Cache::setFacadeApplication($this->app);
		\Illuminate\Support\Facades\Config::setFacadeApplication($this->app);
		\Illuminate\Support\Facades\Cache::swap($cacheMock = \Mockery::mock('Cache'));
		\Illuminate\Support\Facades\Config::swap($configMock = \Mockery::mock('Config'));

		$cacheMock->shouldReceive('get')
				->once()
				->andReturn($value);
		
		$configMock->shouldReceive('get')
			->once()
			->with('orchestra/memory::cache.cachemock', array())
			->andReturn(array());

		$this->stub = new \Orchestra\Memory\Drivers\Cache($this->app, 'cachemock');
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
	 * Test Orchestra\Memory\Drivers\Cache::initiate() method.
	 *
	 * @test
	 * @group support
	 */
	public function testInitiateMethod()
	{	
		$this->assertEquals('Orchestra', $this->stub->get('name'));
		$this->assertEquals('default', $this->stub->get('theme.backend'));
		$this->assertEquals('default', $this->stub->get('theme.frontend'));
	}

	/**
	 * Test Orchestra\Memory\Drivers\Cache::shutdown()
	 *
	 * @test
	 * @group support
	 */
	public function testShutdownMethod()
	{
		$cacheMock = \Mockery::mock('Cache')
			->shouldReceive('forever')->once()->andReturn(true);
		\Illuminate\Support\Facades\Cache::swap($cacheMock->getMock());

		$this->stub->shutdown();
	}
}