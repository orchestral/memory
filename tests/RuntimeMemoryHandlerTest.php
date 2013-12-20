<?php namespace Orchestra\Memory\TestCase;

use Mockery as m;
use Orchestra\Memory\RuntimeMemoryHandler;

class RuntimeMemoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Stub instance.
     *
     * @var Orchestra\Memory\RuntimeMemoryHandler
     */
    private $stub = null;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->stub = new RuntimeMemoryHandler('stub', array());
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
     * Test Orchestra\Memory\RuntimeMemoryHandler::__construct()
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
     * Test Orchestra\Memory\RuntimeMemoryHandler::initiate()
     *
     * @test
     */
    public function testInitiateMethod()
    {
        $this->assertEquals(array(), $this->stub->initiate());
    }

    /**
     * Test Orchestra\Memory\RuntimeMemoryHandler::finish()
     *
     * @test
     */
    public function testFinishMethod()
    {
        $this->assertTrue($this->stub->finish());
    }
}
