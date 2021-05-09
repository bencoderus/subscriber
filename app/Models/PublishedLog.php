<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishedLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'has_received' => 'boolean'
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function message(): belongsTo
    {
        return $this->belongsTo(PublishedMessage::class);
    }

    public function subscription(): belongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
