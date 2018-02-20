<?php

namespace Orchestra\Memory\TestCase\Unit\Handlers;

use PHPUnit\Framework\TestCase;
use Orchestra\Memory\Handlers\Runtime;

class RuntimeTest extends TestCase
{
    /**
     * Test Orchestra\Memory\Handlers\Runtime::initiate().
     *
     * @test
     */
    public function it_can_be_initiated()
    {
        $handler = new Runtime('stub', []);

        $this->assertEquals([], $handler->initiate());
    }

    /**
     * Test Orchestra\Memory\Handlers\Runtime::finish().
     *
     * @test
     */
    public function it_can_be_closed()
    {
        $handler = new Runtime('stub', []);

        $this->assertTrue($handler->finish());
    }
}
