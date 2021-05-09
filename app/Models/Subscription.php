<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'has_received' => 'boolean'
    ];

    public function topic(): belongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
