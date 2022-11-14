<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>
    <style>
        *{
            font-family: sans-serif;
            color: #505050;
        }

        .title{
            background-color: gray;
            text-align: right;
            padding: .6rem 1rem;
            margin: 0;
            text-transform: uppercase;
        }

        .title span{
            color: #fff;
        }

        .title-2{
            background-color: rgba(128, 128, 128, 0.297);
            text-align: left;
            padding: .6rem 1rem;
            margin: 0;
            text-transform: uppercase;
        }

        .line{
            text-align: left;
            padding: .6rem 1rem;
            margin: 0;
            font-size: .8rem;
        }

        .center{
            text-align: center;
            display: inline-block;
            font-size: 1.2rem;
            width: 100%;
        }

        .bold{
            font-weight: bold;
        }

        .mb-3{
            margin-bottom: 1rem;
        }

        .mr-2{
            margin-right: 1rem;
            display: inline-block;
        }

        table{
            border-collapse: collapse;
            border: 1px solid gray;
            width: 100%;
            text-align: left ;
            margin-top: 1rem;
        }

        tr,td,th{
            border: 1px solid gray;
            text-align: left;
            padding: 1rem;
        }

        thead th{
            font-weight: normal;
        }

        .card{
            padding: .6rem;
            border: 1px solid #aaa;
            max-width: 250px;
            width: 100%;
            margin-left: auto;
            margin-bottom: 1.4rem;
            margin-top: 1.3rem;
        }

        .date{
            text-align: right;
            margin-top: 2rem;
            margin-bottom: 2.1rem;
            clear: both;
        }

        header{
            border-bottom: 1px solid #aaa;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        header .logo{
            width: 150px;
            height: 150px;
            background-color: #eee;
            float: left;;
        }

        header .content{
            width: 78% !important;
            text-align: center;
            float:right;
            /* background: red; */
        }

        header .content h1{
            font-size: 2rem;
            margin: 0;
        }

        .italic{
            font-style: italic;
        }

        .signature{
            margin-top: 2rem;
            text-align: right;
            padding: 3rem 5rem;
        }

        .website{
            margin: 0;
        }

        footer{
            position: absolute;
            width: 100%;
            bottom: 0;
            padding-top: 1rem;
            border-top: 1px solid #505050;
            font-size: .8rem;
        }

    </style>

    

</head>

<body class="facture">
    {{-- <x-header-doc :declare="$clientDeclare ?? false" /> --}}

    <div class="date">
        Douala le {{ date('d M Y') }}
    </div>

    <div class="card">
        <div class="line">
            <span class="center"><span class="bold"> {{ $commande->customer->firstname }} {{ $commande->customer->lastname }}</span></span>
        </div>
    
        <div class="line">
            <span>Email : <span class=""> {{ $commande->customer->email }} </span></span>
        </div>
    
        <div class="line">
            <span>Tél : <span class=""> {{ $commande->customer->tel }} </span></span>
        </div>
    </div>
    
    @php
        $total =  $commande->cout;
    @endphp

    <span class="bold">FACTURE N&deg;{{ $commande->invoice->id }}/{{ date('m/Y') }}/</span>
    <table>
        <thead>
            <th>Nom du produit</th>
            <th>Quantité</th>
            <th>Prix unitaire</th>
            <th>total</th>
        </thead>
        <tbody>
            @foreach ($commandes as $commande)
                <tr>
                    <td class="">{{ ucfirst($commande->product->name) }}</td>
                    <td>{{ $commande->qte }}</td>
                    <td>{{ $commande->prix_de_vente !== $commande->product->prix_unitaire ? $commande->prix_de_vente : $commande->product->prix_unitaire }}</td>
                    <td>{{ $commande->qte * (int)implode('',explode('.',$commande->prix_de_vente !== $commande->product->prix_unitaire ? $commande->prix_de_vente : $commande->product->prix_unitaire)) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: right">Montant Total</th>
                <th>{{ $total}} F</th>
            </tr>
        </tfoot>
    </table>

    <div class="signature">
        <a href="https://solumat-sarl.com/">La Direction</a>
    </div>

    {{-- <x-facture-footer /> --}}
</body>

</html>
