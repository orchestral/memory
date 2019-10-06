<?php

namespace Orchestra\Memory\Tests\Feature\Handlers;

use Orchestra\Memory\Tests\Feature\TestCase;
use Orchestra\Support\Facades\Memory;

class RuntimeTest extends TestCase
{
    /** @test */
    public function it_define_proper_signature()
    {
        $handler = Memory::make('runtime.stub')->getHandler();

        $this->assertEquals('runtime', $handler->getStorageName());
        $this->assertEquals('stub', $handler->getName());
    }
}
