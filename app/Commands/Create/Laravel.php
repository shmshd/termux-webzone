<?php

namespace App\Commands\Create;

use Illuminate\Console\Scheduling\Schedule;
use Laminas\Text\Figlet\Figlet;
use LaravelZero\Framework\Commands\Command;

class Laravel extends Command
{
    protected $dir;

    protected $path;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'create:laravel
							{name?}
							{--path=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create laravel projects';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->callSilently('settings:init');
        $this->dir = $this->getData()['project_dir'];
        $this->line(exec('clear'));
        $this->logo();
        $this->init();
    }

    public function getData()
    {
        $json_object = file_get_contents(config('settings.PATH') . '/settings.json');
        $data = json_decode($json_object, true);
        return $data;
    }

    public function logo()
    {
        $figlet = new Figlet();
        $this->comment($figlet->setFont(config('logo.font'))->render("Laravel"));
    }

    private function init()
    {
        //name of project
        if (!empty($this->argument('name'))) {
            $this->name = $this->argument('name');
        } else {
            //planing to generate random names from a new package.
            $this->name = 'something';
        }

        //set path
        if (!empty($this->option('path'))) {
            $this->path = $this->option('path');
        } elseif (!empty($this->dir) && is_dir($this->dir)) {
            $this->path = $this->dir;
        } else {
            $this->path = '/sdcard';
        }

        //check if directory exists
        if (!$this->checkDir()) {
            exit();
        } else {
            $this->line(exec('tput sgr0'));
            $this->info('Creating laravel app');
            $this->newline();
            $this->create();
            $this->newline();
            $this->comment("Laravel App created successfully on {$this->path}/{$this->name}");
        }
    }

    private function checkDir()
    {
        if (file_exists($this->path . '/' . $this->name)) {
            $this->error("A duplicate file/directory found in the path. Please choose a better name.");
            return false;
        } else {
            return true;
        }
    }

    private function create()
    {
        $cmd = "cd {$this->path} && composer create-project laravel/laravel \"{$this->name}\"";
        $this->exec($cmd);
    }

    private function exec($command)
    {
        $this->line(exec($command));
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
