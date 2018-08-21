<?php

namespace App\Console\Commands;

use Helpers\SlackNotification;
use Illuminate\Console\Command;

abstract class BaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        \Log::info('Start '.$this->signature);
        SlackNotification::send('Start '.$this->signature. ' at ' . (new \DateTime())->format('Y-m-d H:i:s'), "Info");
        try {
            $this->doCommand();
        } catch (\Exception $e) {
            \Log::error($e);
        }
        \Log::info('Stop '.$this->signature);
        SlackNotification::send('Stop '.$this->signature. ' at ' . (new \DateTime())->format('Y-m-d H:i:s'), "Info");
    }

    abstract public function doCommand();
}
