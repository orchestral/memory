<?php namespace Orchestra\Memory\Tests\Drivers;

use Mockery as m;
use Orchestra\Memory\Drivers\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase {
	
	/**
	 * Application mock instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	private $app = null;

	/**
	 * Stub instance.
	 *
	 * @var Orchestra\Memory\Drivers\Cache
	 */
	private $stub = null;

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		

		$this->app = m::mock('Illuminate\Foundation\Application');
		$cache = m::mock('Cache');
		$config = m::mock('Config');

		$value = array(
			'name' => 'Orchestra',
			'theme' => array(
				'backend' => 'default',
				'frontend' => 'default',
			),
		);

		$this->app->shouldReceive('instance')->andReturn(true);
		$cache->shouldReceive('get')->once()->andReturn($value);
		$config->shouldReceive('get')->once()->with('orchestra/memory::cache.cachemock', array())->andReturn(array());

		\Illuminate\Support\Facades\Cache::setFacadeApplication($this->app);
		\Illuminate\Support\Facades\Config::setFacadeApplication($this->app);
		\Illuminate\Support\Facades\Cache::swap($cache);
		\Illuminate\Support\Facades\Config::swap($config);

		$this->stub = new Cache($this->app, 'cachemock');
	}
	
	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		unset($this->app);
		unset($this->stub);
		m::close();
	}

	/**
	 * Test Orchestra\Memory\Drivers\Cache::initiate() method.
	 *
	 * @test
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
	 */
	public function testShutdownMethod()
	{
		$cache = m::mock('Cache');

		$cache->shouldReceive('forever')->once()->andReturn(true);
		\Illuminate\Support\Facades\Cache::swap($cache);

		$this->stub->shutdown();
	}
}
