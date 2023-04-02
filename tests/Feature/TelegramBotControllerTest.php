<?php

use App\Models\User;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('displays telegram bot page when bot does not exist', function () {
    $response = $this->actingAs($this->user)->get('/bot');

    $response
        ->assertSuccessful()
        ->assertSee('Update Telegram bot name and API token.')
        ->assertViewHas('bot', fn(TelegraphBot $bot) => !$bot->exists);
});

it('displays telegram bot page when bot exists', function () {
    $bot = TelegraphBot::factory()->create();

    $response = $this->actingAs($this->user)->get('/bot');

    $response
        ->assertSuccessful()
        ->assertSee('Update Telegram bot name and API token.')
        ->assertViewHas('bot', $bot);
});

it('creates telegram bot and registers the webhook', function () {
    Telegraph::fake();

    expect(TelegraphBot::first())->toBeNull();

    $response = $this->actingAs($this->user)
        ->from('/bot')
        ->put(route('bot.update'), [
            'name' => 'My bot',
            'token' => 'xxx:yyy',
        ]);

    $response->assertValid()->assertRedirect('/bot');
    expect($bot = TelegraphBot::first())
        ->not->toBeNull()
        ->and($bot)
        ->name->toBe('My bot')
        ->token->toBe('xxx:yyy');
    Telegraph::assertRegisteredWebhook();
});

it('updates telegram bot and registers the webhook', function () {
    Telegraph::fake();
    $bot = TelegraphBot::factory()->create([
        'name' => 'Old bot name',
        'token' => 'old:token',
    ]);

    $response = $this->actingAs($this->user)
        ->from('/bot')
        ->put(route('bot.update'), [
            'name' => 'New bot name',
            'token' => 'new:token',
        ]);

    $response->assertValid()->assertRedirect('/bot');
    expect($bot->fresh())
        ->name->toBe('New bot name')
        ->token->toBe('new:token');
    Telegraph::assertRegisteredWebhook();
});
