<?php namespace Orchestra\Memory\Abstractable\TestCase;

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInformations()
    {
    	$stub = new StubHandler('stub-handler', array());

    	$this->assertEquals('stub-handler', $stub->getName());
    	$this->assertEquals('stub', $stub->getStorageName());
    }
}

class StubHandler extends \Orchestra\Memory\Abstractable\Handler
{
	protected $storage = 'stub';
}
