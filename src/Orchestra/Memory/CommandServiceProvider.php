<?php namespace Orchestra\Memory;

use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['orchestra.commands.memory'] = $this->app->share(function()
		{
			return new Console\MemoryCommand;
		});

		$this->commands('orchestra.commands.memory');
	}
}
