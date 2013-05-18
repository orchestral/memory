<?php namespace Orchestra\Memory;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class MemoryServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerMemory();
		$this->registerMemoryCommand();
	}

	/**
	 * Register the service provider for Memory.
	 *
	 * @return void
	 */
	protected function registerMemory()
	{
		$this->app['orchestra.memory'] = $this->app->share(function($app)
		{
			return new MemoryManager($app);
		});

		$this->app->booting(function()
		{
			$loader = AliasLoader::getInstance();
			$loader->alias('Orchestra\Memory', 'Orchestra\Support\Facades\Memory');
		});
	}

	/**
	 * Register the service provider for Memory command.
	 *
	 * @return void
	 */
	protected function registerMemoryCommand()
	{
		$this->app['orchestra.commands.memory'] = $this->app->share(function($app)
		{
			return new Console\MemoryCommand;
		});

		$this->commands('orchestra.commands.memory');
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('orchestra/memory', 'orchestra/memory');
		$this->registerMemoryEvent();
	}

	/**
	 * Register memory events during booting.
	 *
	 * @return void
	 */
	protected function registerMemoryEvent()
	{
		$app = $this->app;

		$app->after(function($request, $response) use ($app)
		{
			$app['orchestra.memory']->finish();
		});
	}
}
