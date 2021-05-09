<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublishedLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'has_received' => 'boolean'
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function message()
    {
        return $this->belongsTo(PublishedMessage::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
