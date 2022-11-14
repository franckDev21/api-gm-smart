<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        # create roles
        $super = Role::create([
            'name' => 'super',
        ]);

        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'administrateur principal',
        ]);

        $gerant = Role::create([
            'name' => 'gerant',
            'display_name' => 'Gerant de la boutique',
        ]);

        $caissier = Role::create([
            'name' => 'caissier',
            'display_name' => 'caissier de la boutique',
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'display_name' => 'utilisateur',
        ]);

        # create permissions
        // module USER
        $showUser = Permission::create([
            'name' => 'show-user',
            'display_name' => 'Show user'
        ]);
        $createUser = Permission::create([
            'name' => 'create-user',
            'display_name' => 'Create user'
        ]);
        $editUser = Permission::create([
            'name' => 'edit-user',
            'display_name' => 'Edit user'
        ]);
        $deleteUser = Permission::create([
            'name' => 'delete-user',
            'display_name' => 'Delete user'
        ]);

        // module product
        $showProduct = Permission::create([
            'name' => 'show-product',
            'display_name' => 'Show product'
        ]);
        $createProduct = Permission::create([
            'name' => 'create-product',
            'display_name' => 'Create product'
        ]);
        $editProduct = Permission::create([
            'name' => 'edit-product',
            'display_name' => 'Edit product'
        ]);
        $deleteProduct = Permission::create([
            'name' => 'delete-product',
            'display_name' => 'Delete product'
        ]);

        // module Customer
        $showCustomer = Permission::create([
            'name' => 'show-customer',
            'display_name' => 'Show customer'
        ]);
        $createCustomer = Permission::create([
            'name' => 'create-customer',
            'display_name' => 'Create customer'
        ]);
        $editCustomer = Permission::create([
            'name' => 'edit-customer',
            'display_name' => 'Edit customer'
        ]);
        $deleteCustomer = Permission::create([
            'name' => 'delete-customer',
            'display_name' => 'Delete customer'
        ]);


        // module Caisse
        $showCaisse = Permission::create([
            'name' => 'show-caisse',
            'display_name' => 'Show caisse'
        ]);
        $createCaisse = Permission::create([
            'name' => 'create-caisse',
            'display_name' => 'Create caisse'
        ]);
        $editCaisse = Permission::create([
            'name' => 'edit-caisse',
            'display_name' => 'Edit caisse'
        ]);
        $deleteCaisse = Permission::create([
            'name' => 'delete-caisse',
            'display_name' => 'Delete caisse'
        ]);

        // module Order
        $showOrder = Permission::create([
            'name' => 'show-order',
            'display_name' => 'Show order'
        ]);
        $createOrder = Permission::create([
            'name' => 'create-order',
            'display_name' => 'Create order'
        ]);
        $editOrder = Permission::create([
            'name' => 'edit-order',
            'display_name' => 'Edit order'
        ]);
        $deleteOrder = Permission::create([
            'name' => 'delete-order',
            'display_name' => 'Delete order'
        ]);

        // module Invoice
        $showInvoice = Permission::create([
            'name' => 'show-invoice',
            'display_name' => 'Show invoice'
        ]);
        $createInvoice = Permission::create([
            'name' => 'create-invoice',
            'display_name' => 'Create invoice'
        ]);
        $editInvoice = Permission::create([
            'name' => 'edit-invoice',
            'display_name' => 'Edit invoice'
        ]);
        $deleteInvoice = Permission::create([
            'name' => 'delete-invoice',
            'display_name' => 'Delete invoice'
        ]);

        // module company
        $showCompany = Permission::create([
            'name' => 'show-company',
            'display_name' => 'Show company'
        ]);
        $createCompany = Permission::create([
            'name' => 'create-company',
            'display_name' => 'Create company'
        ]);
        $editCompany = Permission::create([
            'name' => 'edit-company',
            'display_name' => 'Edit company'
        ]);
        $deleteCompany = Permission::create([
            'name' => 'delete-company',
            'display_name' => 'Delete company'
        ]);

        # attach permissions to roles 
        // admin role
        $admin->attachPermissions([
            // company
            $showCompany,
            $createCompany,
            $editCompany,
            $deleteCompany,

            // users | creer tous les type de users
            $showUser,
            $editUser,
            $createUser,
            $deleteUser
        ]);

        // Gerant
        $gerant->attachPermissions([
            // users | creer tous les users avec un role !== 'GERANT'
            $showUser,
            $editUser,
            $createUser,
            $deleteUser,

            // invoice
            $showInvoice,
            $createInvoice,
            $editInvoice,
            $deleteInvoice,

            // company | voir | editer
            $showCompany,
            $editCompany,
            $createCompany,
            $deleteCompany,

            // caisse
            $showCaisse,
            $createCaisse,
            $editCaisse,
            $deleteCaisse,

            // customer
            $showCustomer,
            $createCustomer,
            $editCustomer,
            $deleteCustomer,

            // order
            $showOrder,
            $editOrder,
            $createOrder,
            $deleteOrder,

            // Product
            $showProduct,
            $editProduct,
            $createProduct,
            $deleteProduct
        ]);

        // caissier
        $caissier->attachPermissions([
            // caisse
            $showCaisse,
            $createCaisse,
            $editCaisse,
            $deleteCaisse,

            // order
            $showOrder
        ]);

        // utilisateur
        $userRole->attachPermissions([
            // order
            $showOrder,
            $editOrder,
            $createOrder,
            $deleteOrder,

            // Product
            $showProduct,
            $editProduct,
            $createProduct,
            $deleteProduct
        ]);



        \App\Models\AdminUser::factory(4)->create();
        \App\Models\Company::factory(4)->create();
        \App\Models\User::factory(5)->create()->each(function($user){
            if([1,2,3][rand(0,2)] === 1){
                $user->attachRole('user');
            }else if([1,2,3][rand(0,2)] === 2){
                $user->attachRole('caissier');
            }else{
                $user->attachRole('gerant');
            }
        });

        \App\Models\SuperUser::factory()->create([
            'firstname' => 'All',
            'lastname' => 'H corp',
            'email' => 'info@allhcorp.com',
            'active' => true
        ]);

        // a l'admin super
        $superUser = \App\Models\SuperUser::first();
        $superUser->attachRole($super);

        // a l'admin principal on donne le role admin
        $user = \App\Models\AdminUser::first();
        $user->attachRole($admin);

        \App\Models\Customer::factory(10)->create();

        \App\Models\ProductSupplier::factory(1)->create([
            'name' => "Alibaba",
        ]);
        \App\Models\ProductSupplier::factory(1)->create([
            'name' => "Amazon",
        ]);
        \App\Models\Category::factory(1)->create([
            'name' => "Produit de plomberie",
        ]);
        \App\Models\Category::factory(1)->create([
            'name' => "Electroménager",
        ]);
        \App\Models\Category::factory(1)->create([
            'name' => "Electronique",
        ]);
        \App\Models\Category::factory(1)->create([
            'name' => "Nutrition",
        ]);
        \App\Models\ProductType::factory(1)->create([
            'name' => "VENDU_PAR_PIECE",
            'slug' => 'Vendu par pièce',
            'unite_de_mesure' => 'KG',
        ]);
        \App\Models\ProductType::factory(1)->create([
            'slug' => "Vendu par kg/g",
            'name' => "VENDU_PAR_KG",
            'unite_de_mesure' => 'KG',
        ]);
        \App\Models\ProductType::factory(1)->create([
            'slug' => "Vendu par litre",
            'name' => "VENDU_PAR_LITRE",
            'unite_de_mesure' => 'L',
        ]);
        \App\Models\ProductType::factory(1)->create([
            'slug' => "Vendu par nombre par conteneur",
            'name' => "VENDU_PAR_NOMBRE_PAR_CONTENEUR",
            'unite_de_mesure' => 'KG',
        ]);

        \App\Models\Product::factory(1)->create([
            'name' => "MacBook Pro - 2022",
            'qte_en_stock' => '15',
            'prix_unitaire' => 2400000,
            'type_approvisionnement' => 'Pièce',
            'category_id' => 3,
            'product_supplier_id' => 1,
            'product_type_id' => 1,
            'poids' => 0.5,
            'qte_stock_alert' => 3,
            'is_stock' => true
        ]);

        \App\Models\Product::factory(1)->create([
            'name' => "Frigo samsung ",
            'qte_en_stock' => 8,
            'prix_unitaire' => 650000,
            'type_approvisionnement' => 'Pièce',
            'category_id' => 2,
            'product_supplier_id' => 2,
            'product_type_id' => 1,
            'poids' => 0.5,
            'qte_stock_alert' => 6,
            'is_stock' => true
        ]);
    }
}
