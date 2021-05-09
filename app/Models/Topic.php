<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function subscriptions(): hasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function publishedMessages(): hasMany
    {
        return $this->hasMany(PublishedMessage::class);
    }

    public function publishedLogs(): hasMany
    {
        return $this->hasMany(PublishedLog::class);
    }
}
