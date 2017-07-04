<?php

namespace Orchestra\Memory\TestCase;

use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testGetInformations()
    {
        $stub = new StubHandler('stub-handler', []);

        $this->assertEquals('stub-handler', $stub->getName());
        $this->assertEquals('stub', $stub->getStorageName());
    }
}

class StubHandler extends \Orchestra\Memory\Handler
{
    protected $storage = 'stub';
}
