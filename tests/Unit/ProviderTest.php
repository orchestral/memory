<?php

namespace Orchestra\Memory\TestCase\Unit;

use Mockery as m;
use Orchestra\Memory\Provider;
use PHPUnit\Framework\TestCase;

class ProviderTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Get Mock instance 1.
     *
     * @return MemoryDriverStub
     */
    protected function getStubInstanceOne()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $data = [
            'foo' => [
                'bar' => 'hello world',
            ],
            'username' => 'laravel',
        ];

        $handler->shouldReceive('initiate')->once()->andReturn($data);

        return new Provider($handler);
    }

    /**
     * Get Mock instance 2.
     *
     * @return MemoryDriverStub
     */
    protected function getStubInstanceTwo()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $data = [
            'foo' => [
                'bar' => 'hello world',
            ],
            'username' => 'laravel',
        ];

        $handler->shouldReceive('initiate')->once()->andReturn($data);

        $stub = new Provider($handler);
        $stub->put('foobar', function () {
            return 'hello world foobar';
        });
        $stub->get('hello.world', function () use ($stub) {
            return $stub->put('hello.world', 'HELLO WORLD');
        });

        return $stub;
    }

    /** @test */
    public function it_can_be_constructed()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $handler->shouldReceive('initiate')->once()->andReturn(['foo' => 'foobar']);

        $stub = new Provider($handler);

        $this->assertEquals('foobar', $stub->get('foo'));
        $this->assertEquals($handler, $stub->getHandler());
    }

    /**
     * Test Orchestra\Memory\Drivers\Driver::finish().
     *
     * @test
     */
    public function it_can_be_destructed()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $handler->shouldReceive('initiate')->once()->andReturn(['foo' => 'foobar'])
            ->shouldReceive('finish')->once()->with(['foo' => 'foobar'])->andReturn(true);

        $stub = new Provider($handler);

        $this->assertTrue($stub->finish());
    }

    /** @test */
    public function it_can_get_an_item()
    {
        $stub1 = $this->getStubInstanceOne();
        $stub2 = $this->getStubInstanceTwo();

        $this->assertEquals(['bar' => 'hello world'], $stub1->get('foo'));
        $this->assertEquals('hello world', $stub1->get('foo.bar'));
        $this->assertEquals('laravel', $stub1->get('username'));

        $this->assertEquals(['bar' => 'hello world'], $stub2->get('foo'));
        $this->assertEquals('hello world', $stub2->get('foo.bar'));
        $this->assertEquals('laravel', $stub2->get('username'));

        $this->assertEquals('hello world foobar', $stub2->get('foobar'));
        $this->assertEquals('HELLO WORLD', $stub2->get('hello.world'));
    }

    /**
     * Test Orchestra\Memory\Drivers\Driver::put() method.
     *
     * @test
     */
    public function it_can_set_an_item()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $handler->shouldReceive('initiate')->once()->andReturn([]);

        $stub = new Provider($handler);

        $this->assertEquals([], $stub->all());

        $stub->put('foo', 'foobar');

        $this->assertEquals(['foo' => 'foobar'], $stub->all());
    }

    /** @test */
    public function it_can_forget_an_item()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $data = [
            'hello' => [
                'world' => 'hello world',
            ],
            'username' => 'laravel',
        ];

        $handler->shouldReceive('initiate')->once()->andReturn($data);

        $stub = new Provider($handler);

        $stub->forget('hello.world');

        $this->assertEquals([], $stub->get('hello'));
    }
}
