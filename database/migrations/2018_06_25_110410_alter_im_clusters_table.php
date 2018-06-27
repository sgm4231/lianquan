<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterImClustersTable extends Migration
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
            //$table->string('adminlist',300)->default(null)->comment('管理员ID');//15号有重复名字了
           $table->string('mutelist',500)->default(null)->comment('禁言用户ID');
            $table->tinyInteger('is_mute')->default(null)->comment('是否开启全员禁言：0.关闭，1.开启');
            $table->string('admin_type',300)->default(null)->comment('管理员类型：1.普通管理员，2.主持人，3.讲师');
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
