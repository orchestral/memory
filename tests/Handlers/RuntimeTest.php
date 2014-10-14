<?php namespace Orchestra\Memory\Handlers\TestCase;

use Mockery as m;
use Orchestra\Memory\Handlers\Runtime;

class RuntimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Stub instance.
     *
     * @var \Orchestra\Memory\Handlers\Runtime
     */
    private $stub = null;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->stub = new Runtime('stub', array());
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
     * Test Orchestra\Memory\Handlers\Runtime::__construct()
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
     * Test Orchestra\Memory\Handlers\Runtime::initiate()
     *
     * @test
     */
    public function testInitiateMethod()
    {
        $this->assertEquals(array(), $this->stub->initiate());
    }

    /**
     * Test Orchestra\Memory\Handlers\Runtime::finish()
     *
     * @test
     */
    public function testFinishMethod()
    {
        $this->assertTrue($this->stub->finish());
    }
}
