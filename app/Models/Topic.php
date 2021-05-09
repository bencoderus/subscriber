<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function publishedMessages()
    {
        return $this->hasMany(PublishedMessage::class);
    }

    public function publishedLogs()
    {
        return $this->hasMany(PublishedLog::class);
    }
}
