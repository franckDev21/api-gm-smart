<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'qte',
        'prix_de_vente',
        'type_de_vente'
    ];

    public function order(){
        return $this->belongsTo(Commande::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
