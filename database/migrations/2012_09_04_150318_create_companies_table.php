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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('country');
            $table->string('postal_code')->nullable();
            $table->string('city');
            $table->string('NUI')->nullable();
            $table->string('RCCM')->nullable();
            $table->string('tel')->nullable();
            $table->string('email')->unique();
            $table->integer('number_of_employees');
            $table->foreignId('admin_user_id')
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
        Schema::dropIfExists('companies');
    }
};
