<?php

namespace Orchestra\Memory\Tests\Unit;

use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    /** @test */
    public function it_can_get_information_regarding_the_handler()
    {
        $stub = new class('stub-handler', []) extends \Orchestra\Memory\Handler {
            protected $storage = 'stub';
        };

        $this->assertEquals('stub-handler', $stub->getName());
        $this->assertEquals('stub', $stub->getStorageName());
    }
}
