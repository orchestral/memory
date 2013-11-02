<?php namespace Orchestra\Memory\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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
    protected $description = 'Orchestra\Memory Command';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->call('migrate', array('--package' => 'orchestra/memory'));
    }
}
