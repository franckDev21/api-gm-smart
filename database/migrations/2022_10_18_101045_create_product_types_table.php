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
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->enum('name',[
                'VENDU_PAR_PIECE',
                'VENDU_PAR_KG',
                'VENDU_PAR_LITRE',
                'VENDU_PAR_NOMBRE_PAR_CONTENEUR'
            ]);
            $table->string('slug');
            $table->enum('unite_de_mesure',['KG','G','L','ML']);
            
            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

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
        Schema::dropIfExists('product_types');
    }
};
