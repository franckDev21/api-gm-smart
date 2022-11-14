<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cash;
use App\Models\TotalCash;
use Illuminate\Http\Request;

class CashierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->user()->company_id){
            return Cash::with(['order','user'])
            ->where('company_id',$request->user()->company_id)
            ->latest()->get();
        }else{
            return [];
        }
    }

    public function getTotal(Request $request){
        if($request->user()->company_id){
            return TotalCash::where('company_id',$request->user()->company_id)->first();
        }else{
            return [];
        }
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 
        $data = $request->validate([
            'montant' => 'required',
            'motif' => 'required',
        ]);
        
        
        Cash::create(array_merge([
            'user_id' => $request->user()->id,
            'type' => 'ENTRER',
            'company_id' => $request->user()->company_id
        ],$data));

        $caisse = TotalCash::first();

        if(!$caisse){
            $caisse = TotalCash::create([
                'montant' => 0,
                'company_id' => $request->user()->company_id
            ]);
        }

        $total = $caisse->sum('montant');

        $caisse->update([
            'montant' => (int)$total + (int)$request->montant
        ]);

        return response([
            "message" => "New entry registered!"
        ],201);
    }


    public function output(Request $request){
        

        $data = $request->validate([
            'montant' => 'required',
            'motif' => 'required',
        ]);

        $caisses = TotalCash::while('company_id',$request->user()->company_id)->all();
        
        if(!$caisses->first()){
            TotalCash::create([
                'montant' => 0,
                'company_id' => $request->user()->company_id
            ]);
        }

        $total = $caisses->sum('montant');

        if((int)$request->montant <= $total){
            $caisse = TotalCash::first();

            $caisse->update([
                'montant' => (int)$total - (int)$request->montant
            ]);
            
            Cash::create(array_merge([
                'user_id' => auth()->user()->id,
                'type'    => 'SORTIR',
                'company_id' => $request->user()->company_id
            ],$data));
    
            return response([
                "message" => "New release recorded !"
            ],201);

        }else{
            return response([
                'error' => 'Montant insufissant !'
            ],201);
        }
        
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cash  $cash
     * @return \Illuminate\Http\Response
     */
    public function show(Cash $cash)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cash  $cash
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cash $cash)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cash  $cash
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cash $cash)
    {
        //
    }
}
