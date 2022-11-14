<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'quantite',
        'cout',
        'etat',
        'customer_id',
        'user_id'  ,
        'desc',
        'company_id',
        'as_taxe',
        'total_ht'
    ];

    public function scopeFilter($query,$filters){
      if($filters['search'] ?? false){
        $query->where('etat',$filters['search']);
      }
    }

    public function orderProducts(){
        return $this->hasMany(OrderProduct::class);
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function invoice(){
        return $this->hasOne(Invoice::class);
    } 
}
