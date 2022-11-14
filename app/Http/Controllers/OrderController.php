<?php

namespace App\Http\Controllers;

use App\Http\Resources\V1\InvoiceResourve;
use App\Mail\InvoiceMail;
use App\Models\Cash;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\TotalCash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Order::with(['orderProducts','customer','user'])->latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        // return $request->all();

        // on creer la commande
        $commande = Order::create([
            'reference' => time(),
            'quantite'  => $request->total_qte,
            'desc'      => $request->desc,
            'cout'      => $request->totalCommande,
            'customer_id' => $request->client,
            'user_id'   => $request->user()->id,
            'etat'      => 'IMPAYER',
            'company_id' => $request->user()->company_id,
            'as_taxe' => $request->taxe === 'TVA' || $request->taxe === 'IR',
            'total_ht' => $request->total_ht
        ]);

        // on créé la facture
        Invoice::create([
            'customer_id'     => $request->client,
            'order_id'   => $commande->id,
            'company_id' => $request->user()->company_id,
            'as_tva' => $request->taxe === 'TVA',
            'as_ir' => $request->taxe === 'IR',
            'reference' => Str::upper(Str::replace('...','',Str::limit(User::with(['company'])->where('id',$request->user()->id)->first()->company->name,3))).'-'.date('Y-m-d') ?? '0'
        ]);

        foreach($request->carts as $cart){
            OrderProduct::create([
                'order_id' => $commande->id,
                'product_id'  => $cart['id'],
                'qte'         => $cart['qte'],
                'prix_de_vente' => $cart['prix_de_vente'],
                'type_de_vente' => $cart['type_de_vente']
            ]);
        }

         // on met a jour le stock
         foreach($request->carts as $cart){
            $cart = (object)$cart;

            $newNbreParCarton = 0;
            

            if($cart->product_type['name'] === 'VENDU_PAR_KG'){
                $nbreUnites =  ((int)$cart->qte_en_stock * (int)$cart->poids) + (int)$cart->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$cart->qte;
                $totalResteEntiere = intval($newNbreUnites / (int)$cart->poids);
                $resteUnites = $newNbreUnites % (int)$cart->poids;
                if($cart->type_de_vente === 'PIECE'){
                    $resteUnites =  $nbreUnites %  (int)$cart->poids;
                }
            }else if($cart->product_type['name'] === 'VENDU_PAR_LITRE'){
                $nbreUnites =  ($cart->qte_en_stock * $cart->qte_en_litre) + $cart->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$cart->qte;
                $totalResteEntiere = intval($newNbreUnites / $cart->qte_en_litre);
                $resteUnites = $newNbreUnites % $cart->qte_en_litre;
                if($cart->type_de_vente === 'PIECE'){
                    $resteUnites =  $nbreUnites %  $cart->qte_en_litre;
                }

            }else if($cart->product_type['name'] === 'VENDU_PAR_NOMBRE_PAR_CONTENEUR'){
                $nbreUnites =  ($cart->qte_en_stock * $cart->nbre_par_carton) + $cart->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$cart->qte;
                $totalResteEntiere = intval($newNbreUnites / $cart->nbre_par_carton);
                $resteUnites = $newNbreUnites % $cart->nbre_par_carton;
                if($cart->type_de_vente === 'PIECE'){
                    $resteUnites =  $nbreUnites %  $cart->nbre_par_carton;
                }
                
            }else if($cart->product_type['name'] === 'VENDU_PAR_PIECE'){
                $nbreUnites = $cart->qte_en_stock + $cart->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$cart->qte;
                $totalResteEntiere = $newNbreUnites;
                $resteUnites = 0;
            }

            if ($newNbreUnites > 0) {                
                // increment qte product
                $product = Product::where('company_id',$request->user()->company_id)->find($cart->id);

                if($cart->type_de_vente === 'PIECE'){
                    $totalResteEntiere = (int)$cart->qte_en_stock - (int)$cart->qte;
                }

                $product->update([
                    'qte_en_stock' => $totalResteEntiere,
                    'is_stock'     => $newNbreUnites > 0,
                    'unite_restante' => $resteUnites
                ]);

                // if ($newNbreParCarton <= $product->qte_stock_alert) {
                //     $product->notify(new ProductStockDangerNotification());
                // }

                // new historic
                ProductHistory::create([
                    'quantite'  => $cart->qte,
                    'type'      => 'SORTIE',
                    'motif'     => 'Commande',
                    'product_id'=> $cart->id,
                    'user_id'   => $request->user()->id,
                    'is_unite'  => true,
                    'company_id' => $request->user()->company_id
                ]);

            } else {
                return response()->json(["error", 'Stock inssufisant !']);
            }
        }

        // Send an email to the user

         return response([
            "message"  =>  "Votre commande  a été enregistré avec succès",
            "order_id" => $commande->id
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $order = Order::with(['orderProducts','customer','user','invoice'])->where('id',$order->id)->latest()->first();
        $products = $order->orderProducts;
        $newProductsTab =  [];

        foreach($products as $product){
            $newProductsTab[] = [
                'id' => $product->id,
                'order_id' => $product->order_id,
                'prix_de_vente' => $product->prix_de_vente,
                'product_id' => $product->product_id,
                'qte' => $product->qte,
                'type_de_vente' => $product->type_de_vente,
                'updated_at' => $product->updated_at,
                'created_at' => $product->created_at,
                'product' => Product::find($product->product_id),
            ];
        }

        return response([
            'order'    => $order,
            'products' => $newProductsTab
        ],201);
    }

    public function getInvoice(Order $order){
        $invoice = Invoice::with(['order','customer'])->where('order_id',$order->id)->first();
        return InvoiceResourve::make($invoice);
    }

    public function payer(Request $request,Order $order){

        if($order->etat === 'PAYER'){
            return response([
                'error' => "This order has already been paid"
            ]);
        }

        // on met a jour l'etat de la order
        $order->update([
            'etat' => 'PAYER'
        ]);

        // on met a jour la caisse
        Cash::create([
            'user_id' => $request->user()->id,
            'type' => 'ENTRER',
            'montant' => (int)implode('',explode('.',$order->cout)),
            'order_id' => $order->id,
            'motif'   => 'Payment of the order',
            'company_id'   => $request->user()->company_id,
        ]);

        $caisse = TotalCash::where('company_id',$request->user()->company_id)->first();

        if(!$caisse){
            $caisse = TotalCash::create([
                'montant' => 0,
                'company_id' => $request->user()->company_id
            ]);
        }

        $total = $caisse->sum('montant');

        $caisse->update([
            'montant' => (int)$total + (int)implode('',explode('.',$order->cout))
        ]);

        return response([
            "message" => "Votre commande a été payée avec succès !"
        ],201);
    }

    public function invoice(Request $request, Order $order){

        $order->update([
            'etat' => 'FACTURER'
        ]);

        $order = Order::with(['customer','orderProducts','user','invoice'])->where('id',$order->id)->first();

        if($request->payer){
            if($order->etat === 'PAYER'){
                return back()->with('warning','Cette commande a déjà été payer');
            }
    
            // on met a jour l'etat de la commande
            $order->update([
                'etat' => 'PAYER'
            ]);
    
            // on met a jour la caisse
            Cash::create([
                'user_id' => $request->user()->id,
                'type' => 'ENTRER',
                'montant' => (int)implode('',explode('.',$order->cout)),
                'order_id' => $order->id,
                'motif'   => 'Paiement de la commande'
            ]);
    
            $caisse = TotalCash::first();
    
            if(!$caisse){
                $caisse = TotalCash::create([
                    'montant' => 0
                ]);
            }
    
            $total = $caisse->sum('montant');
    
            $caisse->update([
                'montant' => (int)$total + (int)implode('',explode('.',$order->cout))
            ]);
        }
            
        $orders = $order->orderProducts;


        if(isset($order->customer->email) ){
            // on envoi un mail l'utilisateur
            Mail::to($order->customer->email)
                ->send(new InvoiceMail($order,$orders));
        }


        return response(['message' => "Email envoyer",201]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        if($order->statut !== 'PAYER'){
            $order->delete();
            return response([
                "message" => "Votre commande a été supprimé avec succès"
            ],201);
        }else{
            return response([
                "error" => "La commande a déjà été payer"
            ],201);
        }
    }
}
