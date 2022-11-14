<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('qte_en_stock')->default(0);
            $table->string('qte_stock_alert')->nullable();
            $table->string('prix_unitaire');
            $table->string('image')->nullable();
            $table->string('type_approvisionnement');
            $table->text('description')->nullable();
            $table->boolean('is_stock')->default(false);

            $table->string('unite_restante')->nullable();
            $table->double('poids')->nullable();
            $table->integer('qte_en_litre')->nullable();
            $table->integer('nbre_par_carton')->nullable();

            $table->foreignId('category_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('product_supplier_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('product_type_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
