<?php

namespace Orchestra\Memory\Console;

use Illuminate\Console\Command;

class MemoryCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'memory:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migration for orchestra/memory package.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $path = 'vendor/orchestra/memory/resources/database/migrations';

        $this->call('migrate', ['--path' => $path]);
    }
}
