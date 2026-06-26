<?php

// app/Models/Menu.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['restaurant_id', 'title', 'active'];

    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function categories(): HasMany { return $this->hasMany(Category::class); }
}