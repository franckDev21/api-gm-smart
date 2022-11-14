<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id','order_id','company_id','reference','as_tva','as_ir'];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

}
