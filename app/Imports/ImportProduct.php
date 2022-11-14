<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProduct implements ToModel
{
    
    protected $company_id;

    public function __construct($company_id)
    {
        $this->company_id = $company_id;
    }

    /**
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return Product::create([
            'name' => $row[0] ?? 'Aucun nom',
            'qte_en_stock' => (int)$row[1] ?? 0,
            'prix_unitaire' => (int)$row[2],
            'unite_restante' => $row[3] ?? 0,
            'type_approvisionnement' => $row[4] ?? '',
            'description' => $row[5] ?? null,
            'poids' => (int)$row[6],
            'qte_en_littre' => (int)$row[7] ?? null,
            'nbre_par_carton' => (int)$row[8] ?? null,
            'product_type_id' => ProductType::where('name',$row[9])->first()->id ?? 1,
            'category_id' => Category::where('name',$row[10])->first()->id ?? 1,
            'product_supplier_id' => Category::where('name',$row[11])->first()->id ?? 1,
            'company_id' => $this->company_id ?? 1
        ]);
    }
}
