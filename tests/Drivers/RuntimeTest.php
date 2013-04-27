<?php namespace Orchestra\Memory\Tests\Drivers;

use Mockery as m;
use Orchestra\Memory\Drivers\Runtime;

class RuntimeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Application mock instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	private $app = null;

	/**
	 * Stub instance.
	 *
	 * @var Orchestra\Memory\Drivers\Runtime
	 */
	private $stub = null;

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		$this->app = m::mock('\Illuminate\Foundation\Application');
		$config    = m::mock('Config');

		$this->app->shouldReceive('instance')->andReturn(true);
		$config->shouldReceive('get')
			->once()->with('orchestra/memory::runtime.stub', array())->andReturn(array());

		\Illuminate\Support\Facades\Config::setFacadeApplication($this->app);
		\Illuminate\Support\Facades\Config::swap($config);

		$this->stub = new Runtime($this->app, 'stub');
	}

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		unset($this->stub);
		unset($this->app);
		m::close();
	}

	/**
	 * Test Orchestra\Memory\Drivers\Runtime::__construct()
	 *
	 * @test
	 */
	public function testConstructMethod()
	{
		$refl    = new \ReflectionObject($this->stub);
		$name    = $refl->getProperty('name');
		$storage = $refl->getProperty('storage');

		$name->setAccessible(true);
		$storage->setAccessible(true);

		$this->assertEquals('runtime', $storage->getValue($this->stub));
		$this->assertEquals('stub', $name->getValue($this->stub));
	}

	/**
	 * Test Orchestra\Memory\Drivers\Runtime::initiate()
	 *
	 * @test
	 */
	public function testInitiateMethod()
	{
		$this->assertTrue($this->stub->initiate());
	}

	/**
	 * Test Orchestra\Memory\Drivers\Runtime::shutdown()
	 *
	 * @test
	 */
	public function testShutdownMethod()
	{
		$this->assertTrue($this->stub->shutdown());
	}
}
