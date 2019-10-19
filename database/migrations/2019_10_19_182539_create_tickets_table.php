<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');//public title
            $table->text('description')->nullable()->default(NULL);//public description
            $table->string('admin_notes')->nullable()->default(NULL);//as the title can be the same, we may need the admin notes to be able to differentiate
            $table->integer('price')->default('0');//price can be negative
            $table->unsignedInteger('vat')->nullable()->default('0');//nullable for when you don't want to work with vat
            $table->tinyInteger('order')->default('0');//in which order a ticket should be displayed
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
        Schema::dropIfExists('tickets');
    }
}
