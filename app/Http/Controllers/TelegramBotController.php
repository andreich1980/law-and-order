<?php

namespace App\Http\Controllers;

use App\Http\Requests\TelegramBotRequest;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TelegramBotController extends Controller
{
    public function edit(): View
    {
        return view('bot.edit', ['bot' => TelegraphBot::firstOrNew()]);
    }

    public function update(TelegramBotRequest $request): RedirectResponse
    {
        $bot = TelegraphBot::firstOrNew();
        $bot->fill($request->validated())->save();

        $bot->registerWebhook()->send();

        return back()->with(['status' => 'telegram-bot-updated']);
    }
}
