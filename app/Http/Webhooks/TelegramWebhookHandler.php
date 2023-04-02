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
    protected function handleChatMessage(Stringable $text): void
    {
        $lawyer = Lawyer::findByChat($this->chat);

        if (!$lawyer) {
            $this->chat
                ->html(__('You are not registered yet. Use `/start` command to start registration.'))
                ->keyboard(Keyboard::make()->buttons([Button::make('/start')->action('start')]))
                ->send();

            return;
        }

        if (!$lawyer->isComplete()) {
            $names = Str::of($text)->split('/\s+/');
            if ($names->count() !== 2 && $names->count() !== 3) {
                $this->chat
                    ->html(__('Wrong name format: it should be LastName FirstName MiddleName (optional)'))
                    ->send();

                return;
            }

            [$lastName, $firstName, $middleName] = $names->pad(3, null);
            $lawyer->update(['last_name' => $lastName, 'first_name' => $firstName, 'middle_name' => $middleName]);
            $name = Str::of($firstName)
                ->when($lastName)
                ->append(' ', $middleName);
            $this->chat->html(__("Nice to meet you, $name 👋🏻"))->send();
            $this->chat
                ->html(
                    __(
                        "You could use this bot after administrator approves your account. I'll let you know once it happens.",
                    ),
                )
                ->send();

            return;
        }

        if (!$lawyer->isActive()) {
            $this->chat
                ->html(
                    __(
                        "You could use this bot after administrator approves your account. I'll let you know once it happens.",
                    ),
                )
                ->send();

            return;
        }

        // Unexpected message
        $this->chat
            ->message(__('Use bot commands to perform different actions. Use `/help` for help.'))
            ->keyboard(Keyboard::make()->buttons([Button::make('/help')->action('help')]))
            ->send();
    }

    public function start(): void
    {
        $lawyer = Lawyer::byChat($this->chat);

        if ($lawyer->isComplete()) {
            $this->chat->html(__('We already know each other. Use relevant commands or `/help` for help.'));

            return;
        }

        $this->chat
            ->html(__('Please, introduce yourself: send your LastName FirstName MiddleName (optional).'))
            ->send();
    }

    public function help(): void
    {
        $commands = collect([
            __('`/start` - registration'),
            __('`/contract` - register a contract'),
            __('`/help` - commands list'),
        ]);

        $this->chat->html($commands->map(fn(string $command) => "* $command\n"))->send();
    }
}
