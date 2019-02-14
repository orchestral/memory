<?php

namespace Orchestra\Memory\TestCase\Feature\Handlers;

use Orchestra\Support\Facades\Memory;
use Orchestra\Memory\TestCase\Feature\TestCase;

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
