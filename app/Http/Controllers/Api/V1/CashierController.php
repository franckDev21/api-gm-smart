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
        $id = $request->id ?? $request->user()->company_id;

        if ($id) {
            return Cash::with(['order', 'user'])
                ->where('company_id', $id)
                ->latest()->get();
        } else {
            return [];
        }
    }

    public function getTotal(Request $request)
    {
        $id = $request->id ?? $request->user()->company_id;

        // return [$request->id, $request->user()->company_id, TotalCash::all(),TotalCash::where('company_id', $id)->first()];

        if ($id) {
            return TotalCash::where('company_id', $id)->first();
        } else {
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
        $id = $request->id ?? $request->user()->company_id;

        $data = $request->validate([
            'montant' => 'required',
            'motif' => 'required',
        ]);


        Cash::create(array_merge([
            'user_id' => $request->user()->id,
            'type' => 'ENTRER',
            'company_id' => $id
        ], $data));

        $caisse = TotalCash::where('company_id', $id)->first();

        if (!$caisse) {
            $caisse = TotalCash::create([
                'montant' => 0,
                'company_id' => $id
            ]);
        }

        $total = $caisse->montant;

        $caisse->update([
            'montant' => (int)$total + (int)$request->montant
        ]);

        return response([
            "message" => "Votre entrée a été enregistrée avec succès !"
        ], 201);
    }


    public function output(Request $request)
    {

        $id = $request->id ?? $request->user()->company_id;

        $data = $request->validate([
            'montant' => 'required',
            'motif' => 'required',
        ]);

        $caisses = TotalCash::where('company_id', $id)->get();

        if (!$caisses->first()) {
            TotalCash::create([
                'montant' => 0,
                'company_id' => $id
            ]);
        }

        $total = $caisses->first()->montant;

        if ((int)$request->montant <= $total) {
            $caisse = TotalCash::where('company_id', $id)->first();

            $caisse->update([
                'montant' => (int)$total - (int)$request->montant
            ]);

            Cash::create(array_merge([
                'user_id' => auth()->user()->id,
                'type'    => 'SORTIR',
                'company_id' => $id
            ], $data));

            return response([
                "message" => "Votre sortie a été enregistré avec succès !"
            ], 201);
        } else {
            return response([
                'error' => 'Montant insufissant !'
            ], 201);
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
