<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable()->default(NULL);
            $table->string('description')->nullable()->default(NULL);
            $table->string('admin_notes');
            $table->tinyInteger('order')->nullable()->default(NULL);
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
        Schema::dropIfExists('ticket_groups');
    }
}
