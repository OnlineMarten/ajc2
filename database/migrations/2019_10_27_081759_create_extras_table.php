<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtrasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extras', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');//public title
            $table->text('description')->nullable()->default(NULL);//public description
            $table->string('admin_notes')->nullable()->default(NULL);//as the title can be the same, we may need the admin notes to be able to differentiate
            $table->integer('price')->default('0');//price can be negative
            $table->unsignedInteger('vat')->nullable()->default(NULL);//nullable for when you don't want to work with vat
            $table->tinyInteger('order')->default('0');//in which order an extra should be displayed
            $table->string('max')->nullable()->default(NULL);//max amount per sale, options: "per ticket" , or a number, example "3". if NULL it's flexible
            $table->boolean('show_on_door_list')->nullable()->default(true);
            $table->jsonb('properties')->nullable()->default(NULL);
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
        Schema::dropIfExists('extras');
    }
}
