<?php namespace Orchestra\Memory;

use Illuminate\Support\ServiceProvider;

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
	}
}