<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'email', 
        'phone', 
        'password',  // Ajout du mot de passe
    ];

    protected $hidden = [
        'password', // Ne pas exposer le mot de passe dans les réponses JSON
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password); // Hasher le mot de passe
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
