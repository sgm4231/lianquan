<?php

declare(strict_types=1);

/*
 * +----------------------------------------------------------------------+
 * |                          ThinkSNS Plus                               |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2017 Chengdu ZhiYiChuangXiang Technology Co., Ltd.     |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.0 of the Apache license,    |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available through the world-wide-web at the following url:           |
 * | http://www.apache.org/licenses/LICENSE-2.0.html                      |
 * +----------------------------------------------------------------------+
 * | Author: Slim Kit Group <master@zhiyicx.com>                          |
 * | Homepage: www.thinksns.com                                           |
 * +----------------------------------------------------------------------+
 */

namespace Zhiyi\PlusGroup\Tests\Feature\API2;

use Zhiyi\Plus\Tests\TestCase;
use Zhiyi\Plus\Models\User as UserModel;
use Illuminate\Foundation\Testing\TestResponse;
use Zhiyi\PlusGroup\Models\Group as GroupModel;
use Zhiyi\PlusGroup\Models\Category as CateModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Zhiyi\PlusGroup\Models\GroupMember as GroupMemberModel;
use Zhiyi\PlusGroup\Models\GroupRecommend as GroupRecommendModel;

class EditGroupTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 修改未审核的圈子.
     *
     * @return mixed
     */
    public function testEditNotAuditGroup()
    {
        $user = factory(UserModel::class)->create();
        $group = $this->createGroupByUser($user, 'public', 0);

        $response = $this
            ->actingAs($user, 'api')
            ->json('POST', "/api/v2/plus-group/groups/{$group->id}", [
                'name' => 'test',
                'summary' => 'test',
            ]);

        $response
            ->assertStatus(403);
    }

    /**
     * 修改已审核的圈子.
     *
     * @return mixed
     */
    public function testEditAuditGroup()
    {
        $user = factory(UserModel::class)->create();
        $group = $this->createGroupByUser($user, 'public', 1);

        $response = $this
            ->actingAs($user, 'api')
            ->json('POST', "/api/v2/plus-group/groups/{$group->id}", [
                'name' => 'test',
                'summary' => 'test',
            ]);

        $response
            ->assertStatus(200);
    }

    /**
     * 更改发帖权限.
     *
     * @return mixed
     */
    public function testEditPostPermissionGroup()
    {
        $user = factory(UserModel::class)->create();
        $group = $this->createGroupByUser($user, 'public', 1);

        $response = $this
            ->actingAs($user, 'api')
            ->json('PATCH', "/api/v2/plus-group/groups/{$group->id}/permissions", [
                'permissions' => ['administrator', 'founder']
            ]);

        $response
            ->assertStatus(204);
    }

    /**
     * 转让圈子.
     *
     * @return mixed
     */
    public function testTransferGroup()
    {
        $user = factory(UserModel::class)->create();
        $other = factory(UserModel::class)->create();
        $group = $this->createGroupByUser($user, 'public', 1);
        $member = $this->addMemberToGroup($group, $other);

        $response = $this
            ->actingAs($user, 'api')
            ->json('PATCH', "/api/v2/plus-group/groups/{$group->id}/owner", [
                'target' => $other->id
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(['message']);
    }

    /**
     * 创建圈子.
     *
     * @param UserModel $user
     * @return GroupModel
     */
    protected function createGroupByUser(UserModel $user, $mode='public', $groupAudit = 1): GroupModel
    {
        $cate = factory(CateModel::class)->create();
        $group = factory(GroupModel::class)->create([
            'user_id' => $user->id,
            'category_id' => $cate->id,
            'mode' => $mode,
            'money' => $mode == 'paid' ? 10 : 0,
            'audit' => $groupAudit,
        ]);

        $memberModel = new GroupMemberModel();
        $memberModel->user_id = $user->id;
        $memberModel->group_id = $group->id;
        $memberModel->audit = 1;
        $memberModel->role = 'founder';
        $memberModel->save();

        return $group;
    }

    /**
     * 添加成员到圈子.
     *
     * @param GroupModel $group
     * @param UserModel $user
     * @param string $role
     * @return GroupMemberModel
     */
    protected function addMemberToGroup(
        GroupModel $group,
        UserModel $user,
        $role = 'member',
        $disabled = 0,
        $audit = 1): GroupMemberModel
    {
        $memberModel = new GroupMemberModel();
        $memberModel->user_id = $user->id;
        $memberModel->group_id = $group->id;
        $memberModel->audit = $audit;
        $memberModel->role = $role;
        $memberModel->disabled = $disabled;
        $memberModel->save();

        return $memberModel;
    }
}
