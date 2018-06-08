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
use Zhiyi\PlusGroup\Models\Post as PostModel;
use Zhiyi\Plus\Models\Comment as CommentModel;
use Zhiyi\PlusGroup\Models\Group as GroupModel;
use Zhiyi\PlusGroup\Models\Category as CateModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Zhiyi\PlusGroup\Models\GroupMember as GroupMemberModel;
use Zhiyi\PlusGroup\Models\GroupReport as GroupReportModel;
use Zhiyi\PlusGroup\Models\GroupRecommend as GroupRecommendModel;

class GroupPostCommentTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 评论帖子.
     *
     * @return mixed
     */
    public function testGroupPostComment()
    {
        $user = factory(UserModel::class)->create();
        $other = factory(UserModel::class)->create();
        $group = $this->createGroupByUser($user);
        $post = factory(PostModel::class)->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);
        $response = $this
            ->actingAs($user, 'api')
            ->json('POST', "/api/v2/plus-group/group-posts/{$post->id}/comments", [
                'body' => 'test',
                'comment_mark' => rand(1000, 9999)
            ]);
        $response
            ->assertStatus(201)
            ->assertJsonStructure(['message']);
    }

    /**
     * 帖子评论列表.
     *
     * @return mixed
     */
    public function testGetGroupPostCommentList()
    {
        $user = factory(UserModel::class)->create();
        $other = factory(UserModel::class)->create();
        $group = $this->createGroupByUser($user);
        $post = factory(PostModel::class)->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);
        $comment = factory(CommentModel::class)->create([
            'user_id' => $user->id,
            'target_user' => 0,
            'body' => 'test',
            'commentable_id' => $post->id,
            'commentable_type' => 'group-posts',
        ]);

        $response = $this
            ->actingAs($user, 'api')
            ->json('get', "/api/v2/plus-group/group-posts/{$post->id}/comments");
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['pinneds', 'comments']);
    }

    /**
     * 删除帖子评论.
     *
     * @return mixed
     */
    public function testDeleteGroupPostComment()
    {
        $user = factory(UserModel::class)->create();
        $other = factory(UserModel::class)->create();
        $group = $this->createGroupByUser($user);
        $post = factory(PostModel::class)->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);
        $comment = factory(CommentModel::class)->create([
            'user_id' => $user->id,
            'target_user' => 0,
            'body' => 'test',
            'commentable_id' => $post->id,
            'commentable_type' => 'group-posts',
        ]);
        $post->increment('comments_count');

        $response = $this
            ->actingAs($user, 'api')
            ->json('DELETE', "/api/v2/plus-group/group-posts/{$post->id}/comments/{$comment->id}");
        $response
            ->assertStatus(204);
    }

    /**
     * 创建圈子.
     *
     * @param UserModel $user
     * @return GroupModel
     */
    protected function createGroupByUser(UserModel $user, $mode = 'public'): GroupModel
    {
        $cate = factory(CateModel::class)->create();
        $group = factory(GroupModel::class)->create([
            'user_id' => $user->id,
            'category_id' => $cate->id,
            'mode' => $mode,
            'money' => $mode == 'paid' ? 10 : 0,
        ]);

        $memberModel = new GroupMemberModel();
        $memberModel->user_id = $user->id;
        $memberModel->group_id = $group->id;
        $memberModel->audit = 1;
        $memberModel->role = 'founder';
        $memberModel->save();

        return $group;
    }
}
