<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'devis_id',
        'user_id',
        'montant',
        'numero_facture',
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

    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
