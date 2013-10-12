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

		$app->after(function() use ($app)
		{
			$app['orchestra.memory']->finish();
		});
	}
}
