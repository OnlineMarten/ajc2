<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtraSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extra_sale', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('extra_id')->index;
            $table->unsignedBigInteger('sale_id')->index;
            $table->foreign('extra_id')->references('id')->on('extras');
            $table->foreign('sale_id')->references('id')->on('sales');
            $table->unsignedInteger('nr')->default('0');
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
        Schema::dropIfExists('extra_sale');
    }
}
