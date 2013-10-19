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
    protected $name = 'orchestra:memory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Orchestra\Memory Command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $action = $this->argument('action');

        if (in_array($action, array('install', 'upgrade'))) {
            $this->fireMigration();
            $this->info('orchestra/memory has been migrated');
        } else {
            $this->error("Invalid action [{$action}].");
        }
    }

    /**
     * Fire migration process.
     *
     * @return void
     */
    protected function fireMigration()
    {
        $this->call('migrate', array('--package' => 'orchestra/memory'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('action', InputArgument::REQUIRED, "Type of action, e.g: 'install', 'upgrade'."),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }
}
