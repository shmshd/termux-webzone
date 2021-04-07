<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ShareTor extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'share:tor';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'portforward through tor';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    	echo exec('clear');
	    $this->torrc = "/storage/emulated/0/laravel-zero/webzone/test/torrc";
    	$this->dir = "/data/data/com.termux/files/usr/bin";
        $this->checkInstallation();
    }
    
    public function logo()
	{
		 $figlet = new \Laminas\Text\Figlet\Figlet();
		$this->comment($figlet->setFont(config('logo.font'))->render(config('logo.name')));
	}
    
    public function checkInstallation()
    {
    	$this->logo();
    	if(!file_exists($this->dir.'/tor')){
			if($this->confirm ("Do you want to install tor?")){
				$this->installtor();
				sleep(1);
				$this->call('share:tor');
			} else {
				$this->error('aborting...');
				exit();
				}
		} else {
			$this->olds = $this->setString();
			foreach($this->olds as $string){
				$this->checkIfInitialized($string['old'], $string['new'], $string['type']);
			}
			$this->comment("You need to restart your termux session to apply changes..");
			}
    	
    }
    
    private function installTor()
    {
    	$this->task("Installing tor", function () {
		
			$cmd = "apt-get install tor -y -qqq";
            exec($cmd);    
		});
    }
    
    public function setString()
    {
	    	$this->old1 = "\nHiddenServiceDir";
			$this->old2 = "\nHiddenServicePort";
			$this->string1 = "\nHiddenServiceDir /data/data/com.termux/files/usr/var/lib/tor/hidden_service/";
			$this->string2 = "\nHiddenServicePort 80 127.0.0.1:8080";
			$array = [['old' => $this->old1, 'new' => $this->string1, 'type' => "hidden service directory"], ['old' => $this->old2, 'new' => $this->string2, 'type' => "hidden service port"]];
			return $array;
    }
    
    private function checkIfInitialized($old, $new, $type)
    {
    	$file = file_get_contents($this->torrc);
	    
		if( strpos(file_get_contents($this->torrc), $old) !== false) {
	        $this->comment("\nPreapplied settings already found. Skipping...");
	    }else{
		$is_initiated = $this->task("configuring {$type} ", function () use (&$new) {
     	
            if($this->rewrite($new))
            { return true; }
            else
			{ return false; }
        });
        
        if($is_initiated){
        	$this->info("\n{$type} initialised successfully..\n");
	        
         }
        
		}
		$this->newLine();
    }
    
    private function rewrite($string)
    {
    	
		$action = file_put_contents($this->torrc, $string, FILE_APPEND | LOCK_EX);
		sleep(1);
		if($action) {
			return true;
		} else {
			return false;
		}
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
