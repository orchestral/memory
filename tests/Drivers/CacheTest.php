<?php namespace Orchestra\Memory\Tests\Drivers;

class CacheTest extends \PHPUnit_Framework_TestCase {

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

		$appMock = \Mockery::mock('Application')
			->shouldReceive('instance')->andReturn(true);
		
		$cacheMock = \Mockery::mock('Cache')
			->shouldReceive('get')->andReturn($value);
		\Illuminate\Support\Facades\Cache::setFacadeApplication(
			$appMock->getMock()
		);
		\Illuminate\Support\Facades\Cache::swap($cacheMock->getMock());

		$this->stub = new \Orchestra\Memory\Drivers\Cache('cachemock');
	}
	
	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
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