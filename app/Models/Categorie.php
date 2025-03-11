<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    use HasFactory;
    protected $fillable = [
        'titre'
    ];
    public function itineraires(): HasMany
    {
        return $this->hasMany(Itineraire::class);
    }
}
