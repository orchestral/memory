<?php namespace Orchestra\Memory\Abstractable\TestCase;

use Mockery as m;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test multiple functionality of Orchestra\Memory\Abstractable\Container.
     *
     * @test
     */
    public function testAttachingMemoryProviders()
    {
        $stub = new ContainerStub;
        $mock = m::mock('\Orchestra\Memory\Drivers\Driver');

        $this->assertFalse($stub->attached());

        $stub->attach($mock);

        $refl = new \ReflectionObject($stub);
        $memory = $refl->getProperty('memory');
        $memory->setAccessible(true);

        $this->assertEquals($mock, $memory->getValue($stub));
        $this->assertEquals($mock, $stub->getMemoryProvider());
        $this->assertTrue($stub->attached());
    }
}

class ContainerStub extends \Orchestra\Memory\Abstractable\Container
{
    //
}
