<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return CustomerResource::collection(Customer::with(['company','orders'])
            ->where('company_id',$request->user()->company_id)
            ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'firstname' => 'required|min:2',
            'lastname' => 'required|min:2',
            'tel' => 'string:max:100',
            'address' => 'string|max:100',
            'email' => 'email'
        ]);

        Customer::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname ,
            'tel' => $request->tel ?? null,
            'address' => $request->address ?? null,
            'email' => $request->email ?? null,
            'company_id' => $request->user()->company_id,
        ]);

        return response([
            'message' => "Votre client a été ajouté avec succès !"
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Customer $customer)
    {
        if($customer->company_id === $request->user()->company_id)
            return response(CustomerResource::make($customer),201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'firstname' => 'required|min:2',
            'lastname' => 'required|min:2',
            'tel' => 'sometimes|string:max:100',
            'address' => 'sometimes|max:100',
            'email' => 'sometimes|email'
        ]);

        $customer->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'tel' => $request->tel,
            'address' => $request->address,
            'email' => $request->email ,
        ]);

        return response([
            'message' => "Les informations de votre client ont bien été mise à jour"
        ],201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response([
            'message' => "Le client '{$customer->company->name}' a bien été supprimé!"
        ],201);
    }
}
