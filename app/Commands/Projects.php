<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Projects extends Command
{
    protected $getRoot;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'project:list';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List all projects';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (file_exists($this->getRoot())) {
            $this->listAll();
        } else {
            $this->error('The folder ' . $this->getRoot . ' was not found.');
        }
    }

    public function getRoot()
    {
        $json_object = file_get_contents(config('settings.PATH') . '/settings.json');
        $data = json_decode($json_object, true);
        return $data['project_dir'];
    }

    public function listAll()
    {
        $headers = ['Projects'];
        $folders = null;

        foreach ($this->getProjects() as $result) {
            if ($result === '.' or $result === '..') {
                continue;
            }

            if (is_dir($this->getRoot() . '/' . $result)) {
                $folders[] = [$result];
            }
        }

        $this->table($headers, $folders);
    }

    private function getProjects()
    {
        $files = scandir($this->getRoot());
        return $files;
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
