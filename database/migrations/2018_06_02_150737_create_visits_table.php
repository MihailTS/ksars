<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip');
            $table->string('user_agent');
            $table->string('hash')->unique();
            $table->unsignedInteger('site_link_id');
            $table->unsignedInteger('visitor_id');
            $table->unsignedInteger('time_on_page');


            $table->foreign('site_link_id')->references('id')->on('site_links');
            $table->foreign('visitor_id')->references('id')->on('visitors');
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
        Schema::dropIfExists('visits');
    }
}
