<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_notices', function (Blueprint $table) {
            $table->increments('notice_id');
            $table->string('id')->comment('群组 ID，群组唯一标识符');
            $table->string('title')->comment('公告标题');
            $table->string('content')->comment('公告内容');
            $table->string('author')->comment('公告发布者');
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
        Schema::dropIfExists('im_notices');
    }
}
