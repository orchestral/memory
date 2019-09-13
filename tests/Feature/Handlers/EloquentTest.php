<?php

namespace Orchestra\Memory\Tests\Feature\Handlers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Memory\Model;
use Orchestra\Memory\Tests\Feature\TestCase;
use Orchestra\Support\Facades\Memory;

class EloquentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Model::insert([
            ['id' => 1, 'name' => 'foo', 'value' => 's:6:"foobar";'],
            ['id' => 2, 'name' => 'hello', 'value' => 's:5:"world";'],
        ]);
    }

    /** @test */
    public function it_can_be_initiated()
    {
        $provider = Memory::make('eloquent');
        $handler = $provider->getHandler();

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $provider);
        $this->assertInstanceOf('\Orchestra\Memory\Handlers\Eloquent', $handler);
        $this->assertSame(['foo' => 'foobar', 'hello' => 'world'], $provider->all());
    }

    /** @test */
    public function it_can_be_closed()
    {
        $provider = Memory::make('eloquent');

        $provider->forget('foo');

        $this->assertTrue($provider->finish());

        $this->assertDatabaseMissing('orchestra_options', ['id' => 1, 'name' => 'foo']);
        $this->assertDatabaseHas('orchestra_options', ['id' => 2, 'name' => 'hello', 'value' => 's:5:"world";']);
    }
}
