<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('description')->nullable()->default(NULL);
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->tinyInteger('min_per_sale')->nullable()->default(NULL);
            $table->tinyInteger('max_per_sale')->nullable()->default(NULL);
            $table->unsignedInteger('capacity');
            $table->tinyInteger('tickets_reserved')->default('0');
            $table->tinyInteger('tickets_sold')->default('0');
            $table->boolean('active')->default(true);
            $table->boolean('sold_out')->default(false);
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
        Schema::dropIfExists('events');
    }
}
