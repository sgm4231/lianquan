<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImClustersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_clusters', function (Blueprint $table) {
            $table->increments('cluster_id');
            $table->string('id')->comment('群组 ID，群组唯一标识符，由环信服务器生成，等同于单个用户的环信 ID');
            $table->string('name')->comment('群组名称，根据用户输入创建，字符串类型');
            $table->string('description')->comment('群组描述，根据用户输入创建，字符串类型');
            $table->boolean('public')->comment('群组类型：true：公开群，false：私有群');
            $table->boolean('membersonly')->comment('加入群组是否需要群主或者群管理员审批。true：是，false：否');
            $table->boolean('allowinvites')->comment('是否允许群成员邀请别人加入此群。 true：允许群成员邀请人加入此群，false：只有群主才可以往群里加人');
            $table->integer('maxusers')->comment('群成员上限，创建群组的时候设置，可修改');
            $table->integer('affiliations_count')->comment('现有成员总数');
            $table->string('affiliations')->comment('现有成员列表，包含了 owner 和 member。例如：“affiliations”:[{“owner”: “13800138001”},{“member”:“v3y0kf9arx”},{“member”:“xc6xrnbzci”}]');
            $table->string('owner')->comment('群主的环信 ID。例如：{“owner”: “13800138001”}');
            $table->string('member')->comment('群成员的环信 ID。例如：{“member”:“xc6xrnbzci”}');
            $table->boolean('invite_need_confirm')->comment('邀请加群，被邀请人是否需要确认。如果是true，表示邀请加群需要被邀请人确认；如果是false，表示不需要被邀请人确认，直接将被邀请人加入群。 该字段的默认值为true');
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
        Schema::dropIfExists('im_clusters');
    }
}
