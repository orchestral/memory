<?php namespace Orchestra\Memory\Drivers\TestCase;

use Mockery as m;

class DriverTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		m::close();
	}

	/**
	 * Get Mock instance 1.
	 * 
	 * @return MemoryDriverStub
	 */
	protected function getMockInstance1()
	{
		$app = array(
			'config' => $config = m::mock('Config')
		);

		$config->shouldReceive('get')
			->with('orchestra/memory::teststub.default', array())->once()->andReturn(array());

		$mock = new MemoryDriverStub($app);
		$mock->put('foo.bar', 'hello world');
		$mock->put('username', 'laravel');

		return $mock;
	}

	/**
	 * Get Mock instance 2.
	 *
	 * @return MemoryDriverStub
	 */
	protected function getMockInstance2()
	{
		$app = array(
			'config' => $config = m::mock('Config')
		);

		$config->shouldReceive('get')
			->with('orchestra/memory::teststub.default', array())->once()->andReturn(array());

		$mock = new MemoryDriverStub($app);
		$mock->put('foo.bar', 'hello world');
		$mock->put('username', 'laravel');
		$mock->put('foobar', function ()
		{
			return 'hello world foobar';
		});
		
		$mock->get('hello.world', function () use ($mock)
		{
			return $mock->put('hello.world', 'HELLO WORLD');
		});

		return $mock;
	}

	/**
	 * Test Orchestra\Memory\Drivers\Driver::initiate()
	 *
	 * @test
	 */
	public function testInitiateMethod()
	{
		$app = array(
			'config' => $config = m::mock('Config')
		);

		$config->shouldReceive('get')
			->with('orchestra/memory::teststub.default', array())->once()->andReturn(array());

		$stub = new MemoryDriverStub($app);
		$this->assertTrue($stub->initiated);
	}

	/**
	 * Test Orchestra\Memory\Drivers\Driver::finish()
	 *
	 * @test
	 */
	public function testFinishMethod()
	{
		$app = array(
			'config' => $config = m::mock('Config')
		);

		$config->shouldReceive('get')
			->with('orchestra/memory::teststub.default', array())->once()->andReturn(array());

		$stub = new MemoryDriverStub($app);
		$this->assertFalse($stub->finish);
		$stub->finish();
		$this->assertTrue($stub->finish);
	}

	/**
	 * Test Orchestra\Memory\Drivers\Driver::get() method.
	 *
	 * @test
	 */
	public function testGetMethod()
	{
		$mock1 = $this->getMockInstance1();
		$mock2 = $this->getMockInstance2();
		
		$this->assertEquals(array('bar' => 'hello world'), $mock1->get('foo'));
		$this->assertEquals('hello world', $mock1->get('foo.bar'));
		$this->assertEquals('laravel', $mock1->get('username'));
		
		$this->assertEquals(array('bar' => 'hello world'), $mock2->get('foo'));
		$this->assertEquals('hello world', $mock2->get('foo.bar'));
		$this->assertEquals('laravel', $mock2->get('username'));
		
		$this->assertEquals('hello world foobar', $mock2->get('foobar'));
		$this->assertEquals('HELLO WORLD', $mock2->get('hello.world'));
	}

	/**
	 * Test Orchestra\Memory\Drivers\Driver::put() method.
	 *
	 * @test
	 */
	public function testPutMethod()
	{
		$app = array(
			'config' => $config = m::mock('Config')
		);

		$config->shouldReceive('get')
			->with('orchestra/memory::teststub.default', array())->once()->andReturn(array());

		$stub = new MemoryDriverStub($app);

		$refl = new \ReflectionObject($stub);
		$data = $refl->getProperty('data');
		$data->setAccessible(true);

		$this->assertEquals(array(), $data->getValue($stub));

		$stub->put('foo', 'foobar');

		$this->assertEquals(array('foo' => 'foobar'), $data->getValue($stub));
	}

	/**
	 * Test Orchestra\Memory\Drivers\Driver::forget() method.
	 *
	 * @test
	 */
	public function testForgetMethod()
	{
		$mock = $this->getMockInstance2();
		$mock->forget('hello.world');

		$this->assertEquals(array(), $mock->get('hello'));
	}
}

class MemoryDriverStub extends \Orchestra\Memory\Drivers\Driver {

	public $initiated  = false;
	public $finish     = false;
	protected $storage = 'teststub';

	public function initiate() 
	{
		$this->initiated = true;
	}

	public function finish() 
	{
		$this->finish = true;
	}
}
