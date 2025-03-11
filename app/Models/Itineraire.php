<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Itineraire extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'titre',
        'duree',
        'image',
        'categorie_id'
    ];
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function destinations(): BelongsToMany {
        return $this->belongsToMany(Destination::class, 'itineraire_destination', 'itineraire_id', 'destination_id');
    }
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

}
