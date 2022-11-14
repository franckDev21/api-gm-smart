<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportUser implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $user = Product::create([
            'firstname' => $row[0],
            'lastname' => $row[1],
            'email' => $row[2],
            'password' => bcrypt($row[2])
        ]);

        return $user;
    }
}
