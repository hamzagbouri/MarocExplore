<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Destination extends Model
{
    use HasFactory;
    protected $fillable = ['logement', 'nom', 'activites', 'plats'];
    public function itineraires(): BelongsToMany
    {
        return $this->belongsToMany(Itineraire::class, 'itineraire_destination', 'destination_id', 'itineraire_id');
    }

}
