<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LogoCompany;
use Illuminate\Http\Request;

class FileController extends Controller
{ 
    public function generateBase64(Request $request){
        $request->validate([
            'image' => 'required'
        ]);
        
        LogoCompany::create([
            'company_id' => $request->user()->company_id,
            'data' => base64_encode($request->image)
        ]);

        return  [
            'message' => base64_encode($request->image)
        ];
    }
}
