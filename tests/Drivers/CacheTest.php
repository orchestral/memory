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
		$this->app = array(
			'cache'  => $cache = m::mock('Cache'),
			'config' => $config = m::mock('Config'),
		);

		$value = array(
			'name' => 'Orchestra',
			'theme' => array(
				'backend' => 'default',
				'frontend' => 'default',
			),
		);

		$cache->shouldReceive('get')->once()->andReturn($value);
		$config->shouldReceive('get')->once()->with('orchestra/memory::cache.cachemock', array())->andReturn(array());
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
	 * Test Orchestra\Memory\Drivers\Cache::initiate() method.
	 *
	 * @test
	 */
	public function testInitiateMethod()
	{	
		$stub = new Cache($this->app, 'cachemock');
		$this->assertEquals('Orchestra', $stub->get('name'));
		$this->assertEquals('default', $stub->get('theme.backend'));
		$this->assertEquals('default', $stub->get('theme.frontend'));
	}

	/**
	 * Test Orchestra\Memory\Drivers\Cache::shutdown()
	 *
	 * @test
	 */
	public function testShutdownMethod()
	{
		$app = $this->app;
		
		$app['cache']->shouldReceive('forever')->once()->andReturn(true);

		with(new Cache($app, 'cachemock'))->shutdown();
	}
}
