<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'address',
        'country',
        'city',
        'tel',
        'email',
        'number_of_employees',
        'postal_code',
        'admin_user_id',
        'active'
    ];

    public function users(){
        return $this->hasMany(User::class);
    }

    public function adminUser(){
        return $this->belongsTo(AdminUser::class);
    }
}
