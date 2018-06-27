<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGrouplevelToImClustersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('im_clusters', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->mediumInteger('grouplevel')->default('0')->comment('群等级状态,默认为0普通群、1官方群、2升级群');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('im_clusters', function (Blueprint $table) {
            //
        });
    }
}
