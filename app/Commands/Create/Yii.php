<?php

namespace App\Commands\Create;

use Illuminate\Console\Scheduling\Schedule;
use Laminas\Text\Figlet\Figlet;
use LaravelZero\Framework\Commands\Command;

class Yii extends Command
{
    protected $dir;

    protected $path;

    protected $type;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'create:yii
							{name?}
							{--type=}
							{--path=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create Yii framework projects';

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
        $this->comment($figlet->setFont(config('logo.font'))->render("Yii"));
    }

    private function init()
    {
        //name of project
        if (!empty($this->argument('name'))) {
            $this->name = $this->argument('name');
        } else {
            //planing to generate random names from a new package.
            $this->name = 'yii-blog';
        }

        //set path
        if (!empty($this->option('path'))) {
            $this->path = $this->option('path');
        } elseif (!empty($this->dir) && is_dir($this->dir)) {
            $this->path = $this->dir;
        } else {
            $this->path = '/sdcard';
        }

        // set project type.
        if (!empty($this->option('type'))) {
            $array = ['basic', 'advanced'];
            if (in_array($this->option('type'), $array)) {
                if ($this->option('type') == 'basic') {
                    $this->type = 'yiisoft/yii2-app-basic';
                } elseif ($this->option('type') == 'advanced') {
                    $this->type = 'yiisoft/yii2-app-advanced';
                } else {
                    $this->type = 'yiisoft/yii2-app-advanced';
                }
            } else {
                $this->error('Invalid type');
                die();
            }
        } else {
            $this->type = 'yiisoft/yii2-app-basic';
        }


        //check if directory exists
        if (!$this->checkDir()) {
            exit();
        } else {
            $this->line(exec('tput sgr0'));
            $this->info('Creating Yii app');
            $this->newline();
            $this->create();
            $this->newline();
            $this->comment("Yii App created successfully on {$this->path}/{$this->name}");
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
        $cmd = "cd {$this->path} && composer create-project {$this->type} \"{$this->name}\"";
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
