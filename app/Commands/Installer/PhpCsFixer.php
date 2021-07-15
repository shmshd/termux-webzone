<?php

declare(strict_types=1);

namespace App\Commands\Installer;

use Illuminate\Console\Scheduling\Schedule;
use Laminas\Text\Figlet\Figlet;
use LaravelZero\Framework\Commands\Command;

class PhpCsFixer extends Command
{
    protected $fixer;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'installer:fixer
							{--uninstall}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install php-cs-fixer';

    /**
     * Execute the console command.
     */
    public function handle(): mixed
    {
        $this->fixer = config('pma.PHP_CS_FIXER_PATH');

        if ($this->option('uninstall')) {
            $this->uninstall();
        } else {
            $this->install();
        }
    }

    public function checkInstallation()
    {
        if (file_exists($this->fixer)) {
            return true;
        }
        return false;
    }

    public function logo(): void
    {
        $figlet = new Figlet();
        $this->info($figlet->render('PHP CS FIXER'));
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    private function uninstall()
    {
        if (! $this->checkInstallation()) {
            $this->error('php-cs-fixer is not installed yet.');
            return false;
        }

        if (! $this->confirm('Do you want to uninstall php-cs-fixer?')) {
            return false;
        }

        $this->info('');
        $this->logo();
        $this->comment("\nUnnstalling php-cs-fixer ...\n");
        $cmd = exec('composer global remove friendsofphp/php-cs-fixer');
        $this->comment("\nUninstalled successfully. \n");
    }

    private function install()
    {
        if ($this->checkInstallation()) {
            $this->error('Php-cs-fixer is already installed. Use "php-cs-fixer fix <folder_name>" to fix directory codes.');
            return false;
        }
        $this->info(exec('clear'));
        $this->info('');
        $this->logo();
        $this->comment("\nInstalling php-cs-fixer...\n");
        $cmd = exec('composer global require friendsofphp/php-cs-fixer');
        $this->comment("\nInstalled successfully. Launch it using \"php-cs-fixer --help\" command.\n");
        $this->initComposerGlobal();
    }

    private function initComposerGlobal(): void
    {
        $this->task('Initialize Command ', function (): void {
            $this->callSilently('composer:global', ['-s' => true]);
        });
    }
}
