<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImGroupImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_group_images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('group_id')->comment('群组ID');
            $table->integer('user_id')->comment('用户ID');
            $table->string('title', 100)->nullable()->default(null)->comment('相册名称');
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

        Schema::dropIfExists('im_group_images');
    }
}
