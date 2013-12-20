<?php namespace Orchestra\Memory\TestCase;

use Mockery as m;
use Orchestra\Memory\MemoryManager;
use Orchestra\Memory\Provider;
use Orchestra\Memory\Abstractable\Handler;
use Orchestra\Memory\MemoryHandlerInterface;

class MemoryManagerTest extends \PHPUnit_Framework_TestCase
{
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
        $this->app = m::mock('\Illuminate\Container\Container');
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
        $app = $this->app;

        $config   = m::mock('\Illuminate\Config\Repository');
        $cache    = m::mock('\Illuminate\Cache\Repository');
        $db       = m::mock('\Illuminate\Database\DatabaseManager');
        $eloquent = m::mock('EloquentHandlerModelMock');
        $query    = m::mock('DB\Query');

        $app->shouldReceive('offsetGet')->times(3)->with('cache')->andReturn($cache)
            ->shouldReceive('offsetGet')->times(4)->with('config')->andReturn($config)
            ->shouldReceive('offsetGet')->once()->with('db')->andReturn($db)
            ->shouldReceive('make')->once()->with('EloquentHandlerModelMock')->andReturn($eloquent);

        $cache->shouldReceive('get')->andReturn(array())->shouldReceive('forever')->andReturn(true);

        $config->shouldReceive('get')->with('orchestra/memory::cache.default', array())->once()->andReturn(array())
            ->shouldReceive('get')->with('orchestra/memory::fluent.default', array())->once()->andReturn(array('table' => 'orchestra_options'))
            ->shouldReceive('get')->with('orchestra/memory::eloquent.default', array())->once()->andReturn(array('model' => $eloquent))
            ->shouldReceive('get')->with('orchestra/memory::runtime.default', array())->once()->andReturn(array());
        $eloquent->shouldReceive('newInstance')->once()->andReturn($eloquent)
            ->shouldReceive('remember')->once()->with(60, 'db-memory:eloquent-default')->andReturn($eloquent)
            ->shouldReceive('all')->andReturn(array());
        $db->shouldReceive('table')->andReturn($query);
        $query->shouldReceive('remember')->once()->with(60, 'db-memory:fluent-default')->andReturn($query)
            ->shouldReceive('get')->andReturn(array());

        $stub = new MemoryManager($app);

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $stub->make('runtime'));
        $this->assertInstanceOf('\Orchestra\Memory\Provider', $stub->make('cache'));
        $this->assertInstanceOf('\Orchestra\Memory\Provider', $stub->make('eloquent'));
        $this->assertInstanceOf('\Orchestra\Memory\Provider', $stub->make('fluent'));
    }

    /**
     * Test that Orchestra\Memory\MemoryManager::makeOrFallback() method.
     *
     * @test
     */
    public function testMakeOrFallbackMethodReturnFluent()
    {
        $app = $this->app;

        $config   = m::mock('\Illuminate\Config\Repository');
        $cache    = m::mock('\Illuminate\Cache\Repository');
        $db       = m::mock('\Illuminate\Database\DatabaseManager');
        $eloquent = m::mock('EloquentHandlerModelMock');
        $query    = m::mock('DB\Query');

        $app->shouldReceive('offsetGet')->times(2)->with('config')->andReturn($config)
            ->shouldReceive('offsetGet')->once()->with('cache')->andReturn($cache)
            ->shouldReceive('offsetGet')->once()->with('db')->andReturn($db);

        $config->shouldReceive('get')->with('orchestra/memory::config.driver', 'fluent.default')->once()->andReturn('fluent.default')
            ->shouldReceive('get')->with('orchestra/memory::fluent.default', array())->once()->andReturn(array('table' => 'orchestra_options'))
            ->shouldReceive('get')->with('orchestra/memory::runtime.orchestra', array())->never()->andReturn(array());
        $db->shouldReceive('table')->once()->with('orchestra_options')->andReturn($query);
        $query->shouldReceive('remember')->once()->with(60, 'db-memory:fluent-default')->andReturn($query)
            ->shouldReceive('get')->once()->andReturn(array());

        $stub = new MemoryManager($app);

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $stub->makeOrFallback());
    }

    /**
     * Test that Orchestra\Memory\MemoryManager::makeOrFallback() method.
     *
     * @test
     */
    public function testMakeOrFallbackMethodReturnRuntime()
    {
        $app = $this->app;

        $config   = m::mock('\Illuminate\Config\Repository');
        $cache    = m::mock('\Illuminate\Cache\Repository');
        $db       = m::mock('\Illuminate\Database\DatabaseManager');
        $query    = m::mock('DB\Query');

        $app->shouldReceive('offsetGet')->times(3)->with('config')->andReturn($config)
            ->shouldReceive('offsetGet')->once()->with('cache')->andReturn($cache)
            ->shouldReceive('offsetGet')->once()->with('db')->andReturn($db);

        $config->shouldReceive('get')->with('orchestra/memory::config.driver', 'fluent.default')->once()->andReturn('fluent.default')
            ->shouldReceive('get')->with('orchestra/memory::fluent.default', array())->once()->andReturn(array('table' => 'orchestra_options'))
            ->shouldReceive('get')->with('orchestra/memory::runtime.orchestra', array())->once()->andReturn(array());
        $db->shouldReceive('table')->once()->with('orchestra_options')->andThrow('Exception');

        $stub = new MemoryManager($app);

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $stub->makeOrFallback());
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
        $app    = $this->app;

        $stub = new MemoryManager($app);

        $stub->extend('stub', function ($app, $name) {
            $handler = new StubMemoryHandler($name, array());

            return new Provider($handler);
        });

        $stub = $stub->make('stub.mock');

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $stub);

        $refl    = new \ReflectionObject($stub);
        $handler = $refl->getProperty('handler');

        $handler->setAccessible(true);

        $this->assertInstanceOf(__NAMESPACE__.'\StubMemoryHandler', $handler->getValue($stub));
    }

    /**
     * Test Orchestra\Memory\MemoryManager::finish() method.
     *
     * @test
     */
    public function testFinishMethod()
    {
        $app    = $this->app;
        $config = m::mock('\Illuminate\Config\Repository');

        $app->shouldReceive('offsetGet')->twice()->with('config')->andReturn($config);

        $config->shouldReceive('get')->with('orchestra/memory::runtime.fool', array())->twice()->andReturn(array());

        $stub = new MemoryManager($app);
        $foo  = $stub->make('runtime.fool');

        $this->assertTrue($foo === $stub->make('runtime.fool'));

        $stub->finish();

        $this->assertFalse($foo === $stub->make('runtime.fool'));
    }

    /**
     * Test that Orchestra\Memory\MemoryManager::make() default driver.
     *
     * @test
     */
    public function testMakeMethodForDefaultDriver()
    {
        $app    = $this->app;
        $config = m::mock('\Illuminate\Config\Repository');

        $app->shouldReceive('offsetGet')->twice()->with('config')->andReturn($config);

        $config->shouldReceive('get')->once()
                ->with('orchestra/memory::runtime.default', array())->andReturn(array())
            ->shouldReceive('get')->once()
                ->with('orchestra/memory::config.driver', 'fluent.default')->andReturn('runtime.default');

        $stub = new MemoryManager($app);
        $stub->make();
    }
}

class StubMemoryHandler extends Handler implements MemoryHandlerInterface
{
    protected $storage = 'stub';

    public function initiate()
    {
        return array();
    }

    public function finish(array $items = array())
    {
        return true;
    }
}
