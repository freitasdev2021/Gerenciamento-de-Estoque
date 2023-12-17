<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->integer('IDCategoria');
            $table->string('NMProduto',50);
            $table->string('DSProduto',250);
            $table->float('VLProduto')->default(0);
            $table->binary('IMGProduto')->nullable(false);
            $table->timestamp('DTEdicao')->useCurrent();
            $table->dateTime('DTVencimento');
            $table->timestamp('DTCadastro')->useCurrent();
            $table->string('SKUProduto',6)->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
