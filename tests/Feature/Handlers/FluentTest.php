<?php

namespace Orchestra\Memory\Tests\Feature\Handlers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Orchestra\Memory\Tests\Feature\TestCase;
use Orchestra\Support\Facades\Memory;

class FluentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('orchestra_options')->insert([
            ['id' => 1, 'name' => 'foo', 'value' => 's:6:"foobar";'],
            ['id' => 2, 'name' => 'hello', 'value' => 's:5:"world";'],
        ]);
    }

    /** @test */
    public function it_can_be_initiated()
    {
        $provider = Memory::make('fluent');
        $handler = $provider->getHandler();

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $provider);
        $this->assertInstanceOf('\Orchestra\Memory\Handlers\Fluent', $handler);
        $this->assertSame(['foo' => 'foobar', 'hello' => 'world'], $provider->all());
    }

    /** @test */
    public function it_can_be_closed()
    {
        $provider = Memory::make('fluent');

        $provider->forget('foo');

        $this->assertTrue($provider->finish());

        $this->assertDatabaseMissing('orchestra_options', ['id' => 1, 'name' => 'foo']);
        $this->assertDatabaseHas('orchestra_options', ['id' => 2, 'name' => 'hello', 'value' => 's:5:"world";']);
    }
}
