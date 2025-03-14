<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatPost extends Model {
    use HasFactory;

    public function cat(): BelongsTo {
        return $this->belongsTo(Cat::class);
    }

    public function post(): BelongsTo {
        return $this->belongsTo(Post::class);
    }
}
