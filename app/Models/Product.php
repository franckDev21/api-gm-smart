<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'qte_en_stock',
        'qte_stock_alert' ,
        'is_stock',
        'prix_unitaire',
        'image',
        'poids',
        'description',
        'type_approvisionnement',
        'unite_restante',
        'qte_en_litre',
        'nbre_par_carton',
        'category_id',
        'product_supplier_id',
        'product_type_id',
        'company_id',
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function productSupplier(){
        return $this->belongsTo(ProductSupplier::class);
    }

    public function productType(){
        return $this->belongsTo(ProductType::class);
    }
}
