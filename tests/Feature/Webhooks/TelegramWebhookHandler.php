<?php

use App\Models\Lawyer;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;

beforeEach(function () {
    $this->bot = TelegraphBot::factory()->create();
});

it('creates a new lawyer when the /start command received', function () {
    Telegraph::fake();
    expect(TelegraphChat::firstWhere('chat_id', 112233))->toBeNull();

    sendMessage('/start', ['id' => 112233])->assertSuccessful();

    $chat = TelegraphChat::firstWhere('chat_id', 112233);
    expect($chat)
        ->not->toBeEmpty()
        ->and(Lawyer::findByChat($chat))
        ->not->toBeEmpty();
    Telegraph::assertSent('Please, introduce yourself', exact: false);
});

it("saves lawyer's registration data when lawyer registration is not complete", function () {
    Telegraph::fake();
    $chat = TelegraphChat::factory()
        ->for($this->bot, 'bot')
        ->create(['chat_id' => 112233]);
    $lawyer = Lawyer::factory()
        ->for($chat, 'chat')
        ->incomplete()
        ->create();
    expect($lawyer->isComplete())->toBeFalse();

    sendMessage('Goodman Saul', ['id' => 112233])->assertSuccessful();

    expect($lawyer->fresh())
        ->last_name->toBe('Goodman')
        ->first_name->toBe('Saul');
    Telegraph::assertSent('Nice to meet you', exact: false);
});

it('does not create new lawyer if it already exists', function () {
    Telegraph::fake();
    $chat = TelegraphChat::factory()
        ->for($this->bot, 'bot')
        ->create(['chat_id' => 112233]);
    Lawyer::factory()
        ->for($chat, 'chat')
        ->incomplete()
        ->create();
    expect(Lawyer::count())->toBe(1);

    sendMessage('/start', ['id' => 112233])->assertSuccessful();

    expect(Lawyer::count())->toBe(1);
});

it("does not accept lawyer's registration data if the format is wrong", function () {
    Telegraph::fake();
    $chat = TelegraphChat::factory()
        ->for($this->bot, 'bot')
        ->create(['chat_id' => 112233]);
    $lawyer = Lawyer::factory()
        ->for($chat, 'chat')
        ->incomplete()
        ->create();
    expect($lawyer)
        ->last_name->toBeEmpty()
        ->first_name->toBeEmpty()
        ->middle_name->toBeEmpty();

    sendMessage('John', ['id' => 112233])->assertSuccessful();

    Telegraph::assertSent('Wrong name format', exact: false);
    expect($lawyer)
        ->last_name->toBeEmpty()
        ->first_name->toBeEmpty()
        ->middle_name->toBeEmpty();
});

function sendMessage(string $text, array $chat = [])
{
    $bot = test()->bot;

    return test()->post("telegraph/$bot->token/webhook", [
        'message' => [
            'message_id' => fake()->randomNumber(6),
            'date' => fake()->unixTime(),
            'chat' => array_merge($chat, [
                'id' => 112233,
                'username' => 'slipping_jimmy',
                'type' => 'private',
            ]),
            'text' => $text,
        ],
    ]);
}
