<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'technicien_id',
        'note',
        'commentaire',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }
}
