<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telephone',
        'type',
        'is_active',
        'prestataire_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function techniciens()
    {
        return $this->hasMany(User::class, 'prestataire_id');
    }

    public function prestataire()
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function ticketsCreated()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function ticketsAssigned()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function devis()
    {
        return $this->hasMany(Devis::class, 'user_id');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class, 'user_id');
    }
}
