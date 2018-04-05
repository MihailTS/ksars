<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSitelinkUrlAndDeleteLastRequestTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_links', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->dropColumn('lastRequestTime');
        });
        Schema::table('site_links', function (Blueprint $table) {
            $table->string('url',2048);
            $table->string('baseURI',2048);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('site_links')) {
            Schema::table('site_links', function (Blueprint $table) {
                $table->dropColumn('url');
                if(Schema::hasColumn('baseURI')){
                    $table->dropColumn('baseURI');
                }
            });
            Schema::table('site_links', function (Blueprint $table) {
                $table->string('url');
                $table->dateTime('lastRequestTime')->nullable();
            });
        }
    }
}
