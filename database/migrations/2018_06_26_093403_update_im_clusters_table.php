<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateImClustersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('im_clusters', function (Blueprint $table) {
            $table->integer('created_at')->change();
            $table->integer('updated_at')->change();
            $table->integer('deleted_at')->change();
            $table->string('member',255)->nullable()->change();
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
