<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstname',
        'lastname',
        'tel',
        'email',
        'city',
        'country',
        'company_id',
        'address'
    ];


    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
