<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class RemoveWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove telegram webhook';

    /**
     * Execute the console command.
     *
     * @throws TelegramSDKException
     */
    public function handle()
    {
        $this->info('Removing..');

        $telegram = new Api(env('TELEGRAM_API_KEY'));
        $response = $telegram->removeWebhook();

        if ($response === true) {
            $this->info('Webhook removed successfully');
            die();
        }

        $this->error('Webhook is not removed!');
    }
}
