<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SetWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set webhook for telegram';

    /**
     * handle command
     *
     * @throws TelegramSDKException
     */
    public function handle()
    {
        $this->info('Setting..');

        $telegram = new Api(env('TELEGRAM_API_KEY'));
        $webhookLink = 'https://2837f01c.ngrok.io/' . env('TELEGRAM_API_KEY') . '/webhook';

        $response = $telegram->setWebhook([
            'url' => $webhookLink,
        ]);

        if ($response === true) {
            $this->info('Webhook set successfully');
            die();
        }

        $this->error('Webhook is not set!');
    }
}
