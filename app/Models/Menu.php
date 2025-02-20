<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu extends Model
{
    use HasFactory;
    protected $fillable = ['menu'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_menus', 'menu_id', 'user_id');
    }
}
