<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeKeywords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropColumn('keyword');
        });
        Schema::table('keywords', function (Blueprint $table) {
            $table->integer('position');
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('keywords')) {
            Schema::table('site_links', function (Blueprint $table) {
                $table->dropColumn('position');
                $table->dropColumn('name');
            });
            Schema::table('keywords', function (Blueprint $table) {
                $table->string('keyword');
            });
        }
    }
}
