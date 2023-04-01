<?php

namespace App\Http\Webhooks;

use App\Models\Lawyer;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class TelegramWebhookHandler extends WebhookHandler
{
    // https://defstudio.github.io/telegraph/quickstart/sending-a-message

    /*
     * 1. Lawyer does not exist for the chat and sends anything (except /start command)
     * - ask to use /start command first
     * 2. Lawyer does not exist for the chat and sends /start command
     * - create a new Lawyer with empty name
     * - greet and ask to introduce themselves
     * 3. Lawyer exists, the name is empty, and they send a message
     * - message should be 2 or 3 words long (first, last or first, middle, last names),
     * otherwise ask for the correct message
     *
     * User sends /start
     * Bot greets the user and asks to introduce themselves
     */

    protected function handleChatMessage(Stringable $text): void
    {
        $lawyer = Lawyer::findByChat($this->chat);
        if (!$lawyer) {
            $this->chat
                ->html('You are not registered yet. Use `/start` command to start registration.')
                ->keyboard(Keyboard::make()->buttons([Button::make('/start')->action('start')]))
                ->send();

            return;
        }

        if (!$lawyer->isComplete()) {
            $names = Str::of($text)->split('/\s+/');
            if ($names->count() !== 2 && $names->count() !== 3) {
                $this->chat->html('Wrong name format: it should be LastName FirstName MiddleName (optional)')->send();

                return;
            }

            [$lastName, $firstName, $middleName] = $names->pad(3, null);
            $lawyer->update(['last_name' => $lastName, 'first_name' => $firstName, 'middle_name' => $middleName]);
            $name = Str::of($firstName)
                ->when($lastName)
                ->append(' ', $middleName);
            $this->chat->html("Nice to meet you, $name 👋🏻")->send();

            return;
        }

        // Unexpected message
        $this->chat
            ->message('To register various documents use appropriate commands. Use `/help` for help.')
            ->keyboard(Keyboard::make()->buttons([Button::make('/help')->action('help')]))
            ->send();
    }

    public function start(): void
    {
        $lawyer = Lawyer::byChat($this->chat);

        if ($lawyer->isComplete()) {
            $this->chat->html('We already know each other.');

            return;
        }

        $this->chat->html('Please, introduce yourself: send your LastName FirstName MiddleName (optional).')->send();
    }

    public function help(): void
    {
        $this->chat
            ->html(
                <<<MD
                    * `/start` - registration
                    * `/contract` - register a contract
                    * `/help` - commands list
                MD
                ,
            )
            ->send();
    }
}
