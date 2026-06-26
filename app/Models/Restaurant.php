<?php

// app/Models/Restaurant.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Restaurant extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'logo', 'phone', 'address', 'theme'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($restaurant) => $restaurant->slug = Str::slug($restaurant->name));
    }

    public function menus(): HasMany { return $this->hasMany(Menu::class); }
}