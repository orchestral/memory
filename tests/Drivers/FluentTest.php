<?php namespace Orchestra\Memory\Drivers\TestCase;

use Mockery as m;
use Orchestra\Memory\Drivers\Fluent;

class FluentTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		m::close();
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
	 */
	public function testInitiateMethod()
	{
		$app = array(
			'config' => $config = m::mock('Config'),
			'db'     => $db = m::mock('DB'),
		);
		
		$query = m::mock('DB\Query');

		$config->shouldReceive('get')
			->once()->with('orchestra/memory::fluent.stub', array())
			->andReturn(array('table' => 'orchestra_options'));

		$db->shouldReceive('table')->andReturn($query);
		$query->shouldReceive('get')->andReturn(static::providerFluent());
			
		$stub = new Fluent($app, 'stub');

		$this->assertInstanceOf('\Orchestra\Memory\Drivers\Fluent', $stub);
		$this->assertEquals('foobar', $stub->get('foo'));
		$this->assertEquals('world', $stub->get('hello'));
	}

	/**
	 * Test Orchestra\Memory\Drivers\Fluent::finish() method.
	 *
	 * @test
	 * @group support
	 */
	public function testFinishMethod()
	{
		$app = array(
			'config' => $config = m::mock('Config'),
			'db'     => $db = m::mock('DB'),
		);

		$selectQuery            = m::mock('DB\Query');
		$checkWithCountQuery    = m::mock('DB\Query');
		$checkWithoutCountQuery = m::mock('DB\Query');

		$config->shouldReceive('get')
			->once()->with('orchestra/memory::fluent.stub', array())->andReturn(array('table' => 'orchestra_options'));
		$checkWithCountQuery->shouldReceive('count')->andReturn(1);
		$checkWithoutCountQuery->shouldReceive('count')->andReturn(0);
		$selectQuery->shouldReceive('update')->with(array('value' => serialize('foobar is wicked')))->once()->andReturn(true)
			->shouldReceive('insert')->once()->andReturn(true)
			->shouldReceive('where')->with('name', '=', 'foo')->andReturn($checkWithCountQuery)
			->shouldReceive('where')->with('name', '=', 'hello')->andReturn($checkWithCountQuery)
			->shouldReceive('where')->with('name', '=', 'stubbed')->andReturn($checkWithoutCountQuery)
			->shouldReceive('get')->andReturn(static::providerFluent())
			->shouldReceive('where')->with('id', '=', 1)->andReturn($selectQuery);
		$db->shouldReceive('table')->andReturn($selectQuery);

		$stub = new Fluent($app, 'stub');

		$stub->put('foo', 'foobar is wicked');
		$stub->put('stubbed', 'Foobar was awesome');
		$stub->finish();
	}
}
