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
        Schema::create('cashes', function (Blueprint $table) {
            $table->id();
            $table->integer('montant');
            $table->enum('type',['ENTRER','SORTIR'])->default('ENTRER');
            $table->text('motif');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade');
                  
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
        Schema::dropIfExists('cashes');
    }
};
