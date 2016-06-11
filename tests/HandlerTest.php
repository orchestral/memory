<?php

namespace Orchestra\Memory\TestCase;

class HandlerTest extends \PHPUnit_Framework_TestCase
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
