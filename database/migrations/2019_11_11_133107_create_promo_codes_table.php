<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->text('description')->nullable()->default(NULL);
            $table->string('remark_on_guestlist')->nullable()->default(NULL);
            $table->unsignedinteger('discount_amount')->nullable()->default(NULL);
            $table->unsignedInteger('discount_perc')->nullable()->default(NULL);
            $table->boolean('apply_to_tickets')->nullable()->default(true);
            $table->boolean('apply_to_extras')->nullable()->default(false);
            $table->boolean('pay_by_invoice')->nullable()->default(false);
            $table->date('valid_until');
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
        Schema::dropIfExists('promo_codes');
    }
}
