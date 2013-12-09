<?php namespace Orchestra\Memory\Drivers\TestCase;

use Mockery as m;
use Illuminate\Container\Container;
use Orchestra\Memory\Drivers\Runtime;

class RuntimeTest extends \PHPUnit_Framework_TestCase
{
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
        $app = new Container;
        $app['config'] = $config = m::mock('Config');

        $config->shouldReceive('get')
            ->once()->with('orchestra/memory::runtime.stub', array())->andReturn(array());

        $this->stub = new Runtime($app, 'stub');
    }

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        unset($this->stub);
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
     * Test Orchestra\Memory\Drivers\Runtime::finish()
     *
     * @test
     */
    public function testFinishMethod()
    {
        $this->assertTrue($this->stub->finish());
    }
}
