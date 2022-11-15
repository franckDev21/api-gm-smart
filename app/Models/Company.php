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

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function cashes()
    {
        return $this->hasMany(Cash::class);
    }

    public function totalCashes()
    {
        return $this->hasMany(TotalCash::class);
    }
}
