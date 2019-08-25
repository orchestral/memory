<?php

namespace Orchestra\Memory\Tests\Feature;

class MemorizableTest extends TestCase
{
    /** @test */
    public function it_can_attach_memory()
    {
        $memory = $this->app['orchestra.memory'];
        $runtime = $memory->make('runtime');

        $stub = new class() {
            use \Orchestra\Memory\Memorizable;
        };

        $this->assertFalse($stub->attached());

        $stub->attach($runtime);

        $this->assertEquals($runtime, $stub->getMemoryProvider());
        $this->assertTrue($stub->attached());
    }
}
