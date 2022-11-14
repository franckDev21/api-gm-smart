<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductSupplier;
use Illuminate\Http\Request;

class ProductSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ProductSupplier::where('company_id',$request->user()->company_id)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required'
        ];

        if($request->address){
            $rules['address'] = 'required';
        }
        if($request->tel){
            $rules['tel'] = 'required';
        }
        if($request->email){
            $rules['email'] = 'required';
        }

        $request->validate($rules);

        ProductSupplier::create([
            'name' => $request->name,
            'address' => $request->address ?? null,
            'tel' => $request->tel ?? null,
            'email' => $request->email ?? null,
            'company_id' => $request->user()->company_id
        ]);

        return response([
            'message' => 'Votre fournisseur a été ajouté avec succès            '
        ],201);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductSupplier  $productSupplier
     * @return \Illuminate\Http\Response
     */
    public function show(ProductSupplier $productSupplier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductSupplier  $productSupplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductSupplier $productSupplier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductSupplier  $productSupplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ProductSupplier $productSupplier)
    {
        if($request->user()->company_id !== $productSupplier->company_id) return null;
        
        $productSupplier->delete();
        return response([
            "message" => "Votre fournisseur a été supprimé avec succès !"
        ],201);
    }
}
