<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lawyer extends Model
{
    use HasFactory;

    protected $fillable = ['telegraph_chat_id', 'last_name', 'first_name', 'middle_name'];

    public static function findByChat(TelegraphChat $chat): ?Lawyer
    {
        return static::firstWhere('telegraph_chat_id', $chat->id);
    }

    public static function byChat(TelegraphChat $chat): static
    {
        return static::firstOrCreate(['telegraph_chat_id' => $chat->id]);
    }

    public function isComplete(): bool
    {
        return $this->first_name && $this->last_name;
    }

    public function isActive(): bool
    {
        return (bool) $this->approved_at;
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'approved_by');
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(TelegraphChat::class, 'telegraph_chat_id');
    }
}
