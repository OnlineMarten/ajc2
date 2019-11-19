<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBasketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baskets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('event_id');
            $table->bigInteger('ticket_id')->default(0);;
            $table->bigInteger('promocode_id')->default(0);
            $table->tinyInteger('nr_tickets');
            $table->string('name')->default("");
            $table->string('email')->default("");
            $table->string('phone')->default("");
            $table->string('country_code')->default("");
            $table->string('dial_code')->default("");
            $table->string('lang')->default('en');
            $table->integer('amount_paid')->default(0);
            $table->integer('total_amount')->default(0);
            $table->integer('total_discount')->default(0);
            $table->text('guestlist_comments')->default(NULL);
            $table->text('admin_comments')->nullable()->default(NULL);
            $table->string('ticket_nr')->nullable()->default("");
            $table->jsonb('extras')->nullable()->default(NULL);
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
        Schema::dropIfExists('baskets');
    }
}
