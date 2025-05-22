<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'statut',
        'category_id',
        'created_by',
        'assigned_to',
        'validated_by',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function devis()
    {
        return $this->hasMany(Devis::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
}
