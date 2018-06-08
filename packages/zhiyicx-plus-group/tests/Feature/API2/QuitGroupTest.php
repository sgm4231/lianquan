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
use Zhiyi\PlusGroup\Models\Group as GroupModel;
use Zhiyi\PlusGroup\Models\Category as CateModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Zhiyi\PlusGroup\Models\GroupMember as GroupMemberModel;

class QuitGroupTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $other;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(UserModel::class)->create();
        $this->other = factory(UserModel::class)->create();
        $this->other->currency()->firstOrCreate(
            ['type' => 1],
            ['sum' => 1000]
        );
    }

    /**
     * 创建圈子.
     *
     * @param UserModel $user
     * @return GroupModel
     */
    protected function createGroup(UserModel $user, $mode='public'): GroupModel
    {
        $cate = factory(CateModel::class)->create();
        $group = factory(GroupModel::class)->create([
            'user_id' => $user->id,
            'category_id' => $cate->id,
            'mode' => $mode,
            'money' => $mode == 'paid' ? 10 : 0,
            'users_count' => 1
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
     * @param UserModel $user
     * @param GroupModel $group
     * @return GroupMemberModel
     */
    protected function createMember(UserModel $user, GroupModel $group): GroupMemberModel
    {
        $member = new GroupMemberModel();
        $member->user_id = $user->id;
        $member->audit = 0;
        $member->role = 'member';
        $group->members()->save($member);

        return $member;
    }

    /**
     * 非圈子成员退出.
     *
     * @return mixed
     */
    public function testNotAuditQuitGroup()
    {
        $group = $this->createGroup($this->user);
        $member = $this->createMember($this->other, $group);

        $this
            ->actingAs($this->other, 'api')
            ->json('DELETE', "/api/v2/plus-group/groups/{$group->id}/exit")
            ->assertStatus(403);
    }

    /**
     * 圈子成员退出.
     *
     * @return mixed
     */
    public function testAuditQuitGroup()
    {
        $group = $this->createGroup($this->user);
        $member = $this->createMember($this->other, $group);
        $member->audit = 1;
        $member->save();

        $this
            ->actingAs($this->other, 'api')
            ->json('DELETE', "/api/v2/plus-group/groups/{$group->id}/exit")
            ->assertStatus(204);
    }

    /**
     * 圈主退出.
     *
     * @return mixed
     */
    public function testFounderQuitGroup()
    {
        $group = $this->createGroup($this->user);
        $member = $this->createMember($this->other, $group);
        $member->audit = 1;
        $member->save();

        $this
            ->actingAs($this->user, 'api')
            ->json('DELETE', "/api/v2/plus-group/groups/{$group->id}/exit")
            ->assertStatus(403);
    }
}
