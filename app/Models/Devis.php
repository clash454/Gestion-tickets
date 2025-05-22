<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devis extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'montant',
        'description',
        'fichier_path',
        'statut',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'validated_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }
}
