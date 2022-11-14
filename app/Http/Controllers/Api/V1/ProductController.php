<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\ProductSupplier;
use App\Models\ProductType;
use App\Models\Supply;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!$request->user()->company_id) return [];
        
        return Product::with(['productSupplier','category','ProductType'])
            ->where('company_id',$request->user()->company_id)
            ->latest()->get();
    }

    public function getTypes(Request $request){
        return ProductType::all();
    }

    public function getSuppliers(Request $request){
        return ProductSupplier::where('company_id',$request->user()->company_id)->get();
    }

    public function getAllHistory(Request $request){
        return ProductHistory::with(['product','user'])
            ->where('company_id',$request->user()->company_id)
            ->latest()->get();
    }

    public function getProcurement(Request $request){
        return Supply::with(['product','user'])
            ->where('company_id',$request->user()->company_id)
            ->latest()->get();
    }
    
    public function addInput(Request $request,Product $product){
        $request->validate([
            'quantite'   => 'required',
            'prix_achat'  => 'required'
        ]);

        // increment qte product
        $product->update([
            'qte_en_stock' => ((int)$product->qte_en_stock += (int)$request->quantite),
            'is_stock'     => ((int)$product->qte_en_stock += (int)$request->quantite) > 0
        ]);

        // on recupere le produit et verificie si son stock est > au stock_alert
        $product = Product::find($product->id);
        // if((int)$product->qte_en_stock > (int)$product->qte_stock_alert){
        //     foreach($product->unreadNotifications as $notification){
        //         $notification->markAsRead();
        //     }
        // }

        // new approvisionnement
        Supply::create([
            'product_id' => $product->id,
            'prix_achat' => $request->prix_achat,
            'quantite'   => $request->quantite,
            'user_id'    => $request->user()->id,
            'is_unite'   => false,
            'company_id' => $request->user()->company_id,
        ]);

        // new hitory
        ProductHistory::create([
            'quantite'  => $request->quantite,
            'type'      => 'ENTRÉE',
            'motif'     => 'Approvisionnement',
            'product_id' => $product->id,
            'user_id'   => $request->user()->id,
            'company_id' => $request->user()->company_id,
        ]);

        return response([
            'message' => 'Votre approvisionnement a été ajouté avec succès !',
            'product' => Product::with(['productSupplier','category','ProductType'])->where('id',$product->id)->first()
        ],201);
    }

    public function addOutput(Request $request,Product $product){
        $product = Product::with(['productSupplier','category','productType'])
            ->where('id',$product->id)
            ->where('company_id',$request->user()->company_id)
            ->first();

        $request->validate([
            'quantite'   => 'required',
            'type'  => 'required',
            'motif'  => 'required',
        ]); 

        if($request->type === 'UNITE'){
            if($product->ProductType->name === 'VENDU_PAR_KG'){
                $nbreUnites =  ($product->qte_en_stock * $product->poids) + $product->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$request->quantite;
                $totalResteEntiere = intval($newNbreUnites / $product->poids);
                $resteUnites = $newNbreUnites % $product->poids;

            }else if($product->ProductType->name === 'VENDU_PAR_LITRE'){
                $nbreUnites =  ($product->qte_en_stock * $product->qte_en_litre) + $product->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$request->quantite;
                $totalResteEntiere = intval($newNbreUnites / $product->qte_en_litre);
                $resteUnites = $newNbreUnites % $product->qte_en_litre;

            }else if($product->ProductType->name === 'VENDU_PAR_NOMBRE_PAR_CONTENEUR'){
                $nbreUnites =  ($product->qte_en_stock * $product->nbre_par_carton) + $product->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$request->quantite;
                $totalResteEntiere = intval($newNbreUnites / $product->nbre_par_carton);
                $resteUnites = $newNbreUnites % $product->nbre_par_carton;

                
            }else if($product->ProductType->name === 'VENDU_PAR_PIECE'){
                return 'jamais';
            }

            if ($newNbreUnites >= 0) {
                // increment qte product
                $product->update([
                    'qte_en_stock' => $totalResteEntiere,
                    'is_stock'     => $newNbreUnites > 0,
                    'unite_restante' => $resteUnites
                ]);

                if ($totalResteEntiere <= $product->qte_stock_alert) {
                    // notification alert stock
                    // $product->notify(new ProductStockDangerNotification());
                }

                // new historic
                ProductHistory::create([
                    'quantite'  => $request->quantite,
                    'type'      => 'SORTIE',
                    'motif'     => $request->motif,
                    'product_id' => $product->id,
                    'user_id'   => $request->user()->id,
                    'is_unite'  => $request->type === 'CARTON' ? false : true,
                    'company_id' => $request->user()->company_id,
                ]);

                return response([
                    "message" => 'Le produit a été retiré avec succès !',
                    'product' => Product::with(['productSupplier','category','ProductType'])->where('id',$product->id)->first()
                ],201);
            } else {
                return response([
                    "error" => "La quantité en stock du $product->name est insuffisant  !"
                ],201);
            }
        }else{
            if (((int)$product->qte_en_stock - (int)$request->quantite) >= 0){
                $newQteProduct = (int)$product->qte_en_stock -= (int)$request->quantite;
                // increment qte product
                $product->update([
                    'qte_en_stock' => $newQteProduct,
                    'is_stock'     =>  ($newQteProduct + $product->unite_restante) > 0 
                ]);

                if ($newQteProduct <= $product->qte_stock_alert) {
                    // notification alert stock
                    // $product->notify(new ProductStockDangerNotification());
                }

                // new historic
                ProductHistory::create([
                    'quantite'  => $request->quantite,
                    'type'      => 'SORTIE',
                    'motif'     => $request->motif,
                    'product_id' => $product->id,
                    'user_id'   => $request->user()->id,
                    'is_unite'  => $request->type === 'CARTON' ? false : true,
                    'company_id' => $request->user()->company_id,
                ]);

                return response([
                    "message" => 'Le produit a été retiré avec succès !',
                    'product' => Product::with(['productSupplier','category','ProductType'])->where('id',$product->id)->first()
                ],201);
                
            }else{
                return response([
                    "error" => "La quantité en stock du $product->name est insuffisant  !"
                ],201);
            }
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

        $rules = [
            'name' => 'required|max:100|min:1',
            'prix_unitaire' => 'required',
            'type_approvisionnement' => 'required',
            'qte_stock_alert' => 'required',
            'category_id' => 'required',
            'product_supplier_id' => 'required',
            'product_type_id' => 'required',
        ];

        if($request->poids){
            $rules['poids'] = 'required';
        }

        if($request->description){
            $rules['description'] = 'sometimes|string|min:1|max:255';
        }

        if($request->nbre_par_carton){
            $rules['nbre_par_carton'] = 'required';
        }

        if($request->image){
            $rules['image'] = 'required|mimes:png,jpg,jpeg,PNG,JPG,jpG,Jpg,jPg,jPG,JPEG,jfif,JFIF,avif,AVIF|max:8000';
    
            $filename = time() . '.' . $request->image->extension();
            $path = $request->image->storeAs('img/products', $filename, 'public');
        }

        if($request->qte_en_litre){
            $rules['qte_en_litre'] = 'required';
        }

        $data = $request->validate($rules);

        $data['image'] = $path ?? null;

        Product::create(array_merge([
            'company_id' => $request->user()->company_id,
        ],$data));

        return response([
            'message' => "Votre produit a bien été ajouté avec succès !            "
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return Product::with(['productSupplier','category','ProductType'])->where('id',$product->id)->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $rules = [
            'name' => 'required|max:100|min:1',
            'prix_unitaire' => 'required',
            'type_approvisionnement' => 'required',
            'qte_stock_alert' => 'required',
            'category_id' => 'required',
            'product_supplier_id' => 'required',
            'product_type_id' => 'required',
        ];

        if($request->poids){
            $rules['poids'] = 'required';
        }

        if($request->description){
            $rules['description'] = 'sometimes|string|min:1|max:255';
        }

        if($request->nbre_par_carton){
            $rules['nbre_par_carton'] = 'required';
        }

        if($request->image){
            $rules['image'] = 'required|mimes:png,jpg,jpeg,PNG,JPG,JPEG,jfif,JFIF,avif,AVIF|max:4000';
    
            $filename = time() . '.' . $request->image->extension();
            $path = $request->image->storeAs('img/products', $filename, 'public');
        }

        if($request->qte_en_litre){
            $rules['qte_en_litre'] = 'required';
        }

        $data = $request->validate($rules);

        $data['image'] = $path ?? ($product->image ?? null);

        $product->update($data);

        return response([
            'message' => "Votre produit a été mise  à jour avec succès !"
        ],201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response([
            'message' => "Votre produit a été supprimé avec succès"
        ],201);
    }
}
