<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImGroupImagesUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_group_images_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_images_id')->comment('相册ID');
            $table->integer('file_id')->comment('文件ID');
            $table->integer('user_id')->comment('用户ID');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('im_group_images_user');
    }
}
