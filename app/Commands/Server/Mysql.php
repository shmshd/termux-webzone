<?php

namespace App\Commands\Server;

use App\ConfigIniter;
use Illuminate\Console\Scheduling\Schedule;
use Laminas\Text\Figlet\Figlet;
use LaravelZero\Framework\Commands\Command;

class Mysql extends Command
{
    protected $mysql;

    protected $port;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'server:mysql
							{--s|--stop}
							{--port=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start MySql Services';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->callSilently('settings:init');
        $this->setPort();
        $this->mysql = config('pma.MYSQL_PATH');
        pcntl_async_signals(true);

        // Catch the cancellation of the action
        pcntl_signal(SIGINT, function () {
            $this->comment("\nShutting down...\n");

            $this->stop();
        });

        $this->checkInstallation();
    }

    private function setPort()
    {
        if (!empty($this->option('port'))) {
            $this->port = $this->option('port');
        } elseif (!empty($this->getPort())) {
            $this->port = $this->getPort();
        } else {
            $this->port = config('pma.MYSQL_PORT');
        }
    }

    public function getPort()
    {
        $json_object = file_get_contents(config('settings.PATH') . '/settings.json');
        $data = json_decode($json_object, true);
        return $data['mysql_port'];
    }

    private function stop()
    {
        $this->task("Kill Mysql processes ", function () {
            $cmd = "killall -9 mysqld 2> /dev/null";
            $response = exec($cmd);
        });
    }

    public function checkInstallation()
    {
        if (!file_exists($this->mysql)) {
            $source = $this->choice(
                "mysql doesn't seem to be installed, do you want to install it now ?",
                [1 => 'install now', 0 => 'cancel']
            );

            if ($source == 'install now' || $source == '1') {
                $this->call('install:mysql');
            }
            if ($source == 'cancel' || $source == '0') {
                $this->info("Good bye");
            }
        } elseif ($this->option('stop')) {
            $this->stop();
        } else {
            $this->start();
        }
    }

    private function start()
    {
        $this->logo();
        $this->comment("mysql Services Started....");
        $this->line("\n");
        $cmd = exec("mysqld --port={$this->port} --gdb");
    }

    public function logo()
    {
        $figlet = new Figlet();
        $this->comment($figlet->setFont(config('logo.font'))->render(config('logo.name')));
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
