<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_ticket', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id')->index;
            $table->unsignedBigInteger('ticket_id')->index;

            $table->foreign('event_id')->references('id')->on('events');//it is ok to delete an event even if there are tickets connected
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('restrict');//can not delete ticket if attached to event
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
        Schema::dropIfExists('event_ticket');
    }
}
