<?php

namespace App\Http\Controllers;

use App\Feedback;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Integer;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use App\User;
use Illuminate\Support\Str;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Controller for handling webhooks from telegram
 *
 * Class WebhookController
 * @package App\Http\Controllers
 */
class WebhookController extends Controller
{
    /**
     * instance of Api telegram client
     *
     * @var Api
     */
    protected $telegram;

    /**
     * handle webhook
     *
     * @param Request $request
     * @throws TelegramSDKException
     */
    public function handle(Request $request)
    {
        $data = $request->all();
        $message = $data['message'];

        $this->telegram = new Api(env('TELEGRAM_API_KEY'));
        $user = User::where('telegram_id', $message['from']['id'])->first();

        if ($user && $user->is_configured) {
            $this->processFeedback($user, $message);
        }

        if (!isset($message['text'])) {
            $response = $this->telegram->getFile([
                'file_id' => $message['photo'][0]['file_id'],
            ]);

            $filePath = str_replace(['"', '\\'], '', $response['file_path']);
            $link = 'https://api.telegram.org/file/bot' . env('TELEGRAM_API_KEY') . '/' . $filePath;

            if (empty($user->avatar)) {
                $user->avatar = $link;
                $user->is_configured = true;
                $user->save();
            }

            $this->sendFeedbackInvitation($message['from']['id']);
            return;
        }

        if ($message['text'] === '/start') {
            if (!User::where('telegram_id', $message['from']['id'])->exists()) {
                User::create([
                    'telegram_id' => $message['from']['id'],
                    'name' => '',
                    'email' => Str::random(6),
                    'password' => Str::random(6),
                ]);

                $this->handleStart($message['chat']['id']);
            }

            return;
        }

        if (empty($user->name)) {
            $user->name = $message['text'];
            $user->save();

            $this->askPhoneNumber($message['from']['id']);
            return;
        }

        if (empty($user->phone_number)) {
            $user->phone_number = $message['text'];
            $user->save();

            $this->askCategory($message['from']['id']);
            return;
        }

        if (empty($user->catergory_id) && is_numeric($message['text'])) {
            $user->category_id = $message['text'];
            $user->save();

            $this->askAvatar($message['from']['id']);
            return;
        }
    }

    /**
     * action on /start
     *
     * @param $chatId
     * @throws TelegramSDKException
     */
    private function handleStart($chatId)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Привет, добро пожаловать! Я справлюсь с тестовым заданием 🙂',
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Давай познакомимся, как тебя зовут?',
        ]);
    }

    /**
     * respond when user again sent /start
     *
     * @param $chatId
     * @throws TelegramSDKException
     */
    private function handleReStart($chatId)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Привет! Рад снова тебе видеть.. Можеш указать имя снова..',
        ]);
    }

    /**
     * ask phone number when it appears name has been sent already
     *
     * @param integer $chatId
     * @throws TelegramSDKException
     */
    private function askPhoneNumber($chatId)
    {
        $keyboard = [
            ['Отправить контакт']
        ];

        $replyMarkup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Для начала использования отправь мне свой контакт',
            'reply_markup' => $replyMarkup
        ]);
    }

    /**
     * ask category
     *
     * @param $chatId
     * @throws TelegramSDKException
     */
    private function askCategory($chatId)
    {
        $keyboard = [
            ['1', '2', '3']
        ];

        $replyMarkup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Отлично! А теперь выбери к какой категории пользователей тебя отнести:',
            'reply_markup' => $replyMarkup
        ]);
    }

    /**
     * ask to send avatar
     *
     * @param $chatId
     * @throws TelegramSDKException
     */
    private function askAvatar($chatId)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Поздравляю, теперь пришли мне фото',
        ]);
    }

    /**
     * send feedback invitation
     *
     * @param $chatId
     * @throws TelegramSDKException
     */
    private function sendFeedbackInvitation($chatId)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Спасибо, теперь вы можете оставить отзыв. Просто отправьте сообщение, мы будем рады положительному фидбеку 😉',
        ]);
    }

    /**
     * process feedback
     *
     * @param User $user
     * @param $message
     */
    private function processFeedback(User $user, $message)
    {
        $text = '';
        if(isset($message['text']))
            $text = $message['text'];

        Feedback::create([
            'user_name' => $user->name,
            'feedback' => $text,
            'user_id' => $user->id,
        ]);
    }
}
