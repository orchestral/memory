<?php

namespace Orchestra\Memory\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Contracts\Memory\Handler as HandlerContract;
use Orchestra\Memory\Handler;
use Orchestra\Memory\Provider;
use Orchestra\Support\Facades\Memory;

class MemoryManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_resolve_drivers()
    {
        $this->assertInstanceOf('\Orchestra\Memory\Provider', Memory::make('runtime'));
        $this->assertInstanceOf('\Orchestra\Memory\Provider', Memory::make('cache'));
        $this->assertInstanceOf('\Orchestra\Memory\Provider', Memory::make('eloquent'));
        $this->assertInstanceOf('\Orchestra\Memory\Provider', Memory::make('fluent'));
    }

    /** @test */
    public function it_can_resolve_driver_without_fallback()
    {
        $provider = Memory::makeOrFallback();
        $handler = $provider->getHandler();

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $provider);
        $this->assertInstanceOf('\Orchestra\Memory\Handlers\Fluent', $handler);
        $this->assertSame('fluent', $handler->getStorageName());
        $this->assertSame('default', $handler->getName());
    }

    /** @test */
    public function it_can_resolve_driver_using_fallback()
    {
        $config = [
            'driver' => 'fluent.default',
            'fluent' => [
                'default' => ['table' => 'orchestra_optionx', 'cache' => true, 'connections' => ['cache' => 'foo']],
            ],
            'runtime' => [
                'orchestra' => [],
            ],
        ];
        Memory::setConfiguration($config);

        $provider = Memory::makeOrFallback();
        $handler = $provider->getHandler();

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $provider);
        $this->assertInstanceOf('\Orchestra\Memory\Handlers\Runtime', $handler);
        $this->assertSame('runtime', $handler->getStorageName());
        $this->assertSame('orchestra', $handler->getName());
    }

    /** @test */
    public function it_can_be_closed()
    {
        $foo = Memory::make('runtime.fool');

        $this->assertSame($foo, Memory::make('runtime.fool'));

        Memory::finish();

        $this->assertFalse($foo === Memory::make('runtime.fool'));
    }

    /** @test */
    public function it_can_make_with_default_driver()
    {
        $config = ['driver' => 'runtime.default'];

        Memory::setConfiguration($config);
        $provider = Memory::make();
        $handler = $provider->getHandler();

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $provider);
        $this->assertInstanceOf('\Orchestra\Memory\Handlers\Runtime', $handler);
        $this->assertSame('runtime', $handler->getStorageName());
        $this->assertSame('default', $handler->getName());
    }

    /** @test */
    public function it_can_set_default_driver()
    {
        Memory::setDefaultDriver('foo');

        $this->assertSame('foo', Memory::getConfiguration()['driver']);
    }

    /** @test */
    public function it_can_be_extended()
    {
        Memory::extend('stub', function ($app, $name) {
            $handler = new class($name, []) extends Handler implements HandlerContract {
                protected $storage = 'stub';

                public function initiate(): array
                {
                    return [];
                }

                public function finish(array $items = []): bool
                {
                    return true;
                }
            };

            return new Provider($handler);
        });

        $provider = Memory::make('stub.mock');
        $handler = $provider->getHandler();

        $this->assertInstanceOf('\Orchestra\Memory\Provider', $provider);
        $this->assertInstanceOf('\Orchestra\Contracts\Memory\Handler', $provider->getHandler());
        $this->assertSame('stub', $handler->getStorageName());
        $this->assertSame('mock', $handler->getName());
    }

    /** @test */
    public function it_throws_exception_given_invalid_driver()
    {
        $this->expectException('InvalidArgumentException');

        $this->withoutExceptionHandling();

        Memory::make('orm');
    }
}
