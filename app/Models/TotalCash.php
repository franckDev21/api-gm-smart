<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalCash extends Model
{
    use HasFactory;

    protected $fillable = ['montant','company_id'];
}
