<?php

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

namespace Zhiyi\PlusGroup\API\Controllers;

use DB;
use Lvht\GeoHash;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Zhiyi\Plus\Models\UserCount;
use Zhiyi\PlusGroup\Models\Group as GroupModel;
use Zhiyi\PlusGroup\API\Requests\CreateGroupRequest;
use Zhiyi\PlusGroup\API\Requests\UpdateGroupRequest;
use Zhiyi\PlusGroup\Models\Category as CategoryModel;
use Zhiyi\Plus\Models\CommonConfig as CommonConfigModel;
use Zhiyi\Plus\Models\WalletCharge as WalletChargeModel;
use Zhiyi\PlusGroup\Models\GroupMember as GroupMemberModel;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Zhiyi\Plus\Packages\Currency\Processes\User as UserProcess;
use Zhiyi\PlusGroup\Models\GroupMemberLog as GroupMemberLogModel;

class GroupsController
{
    // 圈子封闭等级
    protected $mode_level = [
        'paid' => 3,
        'private' => 2,
        'public' => 1,
    ];

    /**
     * List groups of category.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $category
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function index(Request $request, int $category)
    {
        $user_id = $request->user('api')->id ?? 0;

        $limit = (int) $request->query('limit', 15);
        $offset = (int) $request->query('offset', 0);

        $groups = GroupModel::where('audit', 1)
            
            ->where('category_id', $category)
            ->limit($limit)
            ->offset($offset)
            ->orderBy('id', 'desc')
            ->get();

        $joined = GroupMemberModel::whereIn('group_id', $groups->map->id)
            ->where('user_id', $user_id)
            ->get();
        
        $groups = $groups->map(function (GroupModel $group) use ($joined) {
            $group->joined = null;
            $joined->each(function (GroupMemberModel $member) use ($group) {
                if ($member->group_id === $group->id && $member->audit === 1) {
                    $group->joined = $member;

                    return false;
                }
            });

            return $group;
        });

        return response()->json($groups, 200);
    }

    /**
     * Create a group.
     *
     * @param \Zhiyi\PlusGroup\API\Requests\CreateGroupRequest $request
     * @param Zhiyi\PlusGroup\Models\Category $category
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function store(CreateGroupRequest $request, CategoryModel $category, ConfigRepository $config)
    {
        $user = $request->user();

        if ($config->get('plus-group.group_create.need_verified') && ! $user->verified) {
            return response()->json(['message' => ['创建圈子需要用户认证']], 422);
        }

        $values = $request->only(['name', 'location', 'longitude', 'latitude', 'geo_hash', 'summary', 'notice']);

        DB::beginTransaction();

        try {
            $group = new GroupModel();

            foreach ($values as $field => $value) {
                $group->$field = $value;
            }

            if ($request->has('permissions')) {
                $permissions = $request->input('permissions');

                if (! is_array($permissions) || ! count($permissions)) {
                    return response()->json(['message' => '无效的参数'], 422);
                }

                $permissions = array_unique($permissions);

                foreach ($permissions as $permission) {
                    if (! in_array($permission, ['member', 'administrator', 'founder'])) {
                        return response()->json(['message' => '无效的发帖权限参数'], 422);
                    }
                }

                $group->permissions = implode(',', $permissions);
            }

            $group->audit = 0;
            $group->user_id = $user->id;
            $group->money = (int) $request->input('money', 0);
            $group->allow_feed = $request->input('allow_feed') ? 1 : 0;
            $group->mode = in_array($mode = $request->input('mode'), ['public', 'private', 'paid']) ? $mode : 'public';

            $category->groups()->save($group);

            $avatar = $request->file('avatar');
            $group->storeAvatar($avatar);

            $tags = collect($request->input('tags'))->map->id;

            $group->tags()->sync($tags);

            DB::commit();

            return response()->json(['message' => '创建成功，等待审核', 'group' => $group]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update group.
     *
     * @param UpdateGroupRequest $request
     * @param Group $group
     * @return mixed
     */
    public function update(UpdateGroupRequest $request, GroupModel $group)
    {
        $user = $request->user();
        $member = $group->members()->where('user_id', $user->id)->where('audit', 1)->where('disabled', 0)->first();

        // 审核未通过或被禁用
        if (in_array($group->audit, [0, 2, 3])) {
            return response()->json(['message' => '圈子审核未通过或已被禁用,不能进行修改'], 403);
        }

        // 是否为普通成员
        if ($member->role === 'member') {
            return response()->json(['message' => '无权限操作'], 403);
        }

        $mode = in_array($mode = $request->input('mode'), ['public', 'private', 'paid']) ? $mode : $group->mode;

        // 圈子私密程度只能由低到高
        if ($this->mode_level[$mode] < $this->mode_level[$group->mode]) {
            return response()->json(['message' => ['不能修改圈子类型']], 422);
        }

        // 存在未审核成员
        if ($mode != $group->mode && $group->members()->where('audit', 0)->count()) {
            return response()->json(['message' => '当前圈子存在未审核成员,不能修改圈子类型'], 403);
        }

        $values = $request->only(['name', 'location', 'longitude', 'latitude', 'geo_hash', 'category_id',  'summary', 'notice']);

        // 管理员只能修改简介和公告
        if ($member->role === 'administrator') {
            $values = $request->only(['summary', 'notice']);
        }

        foreach ($values as $field => $value) {
            $group->$field = $value;
        }

        if ($request->has('permissions')) {
            $permissions = $request->input('permissions');

            if (! is_array($permissions) || ! count($permissions)) {
                return response()->json(['message' => '无效的参数'], 422);
            }

            $permissions = array_unique($permissions);

            foreach ($permissions as $permission) {
                if (! in_array($permission, ['member', 'administrator', 'founder'])) {
                    return response()->json(['message' => '无效的发帖权限参数'], 422);
                }
            }

            $group->permissions = implode(',', $permissions);
        }

        $group->allow_feed = $request->input('allow_feed') ? 1 : 0;

        // 收费圈不能修改金额和类型
        if ($group->mode !== 'paid') {
            $group->mode = $mode;
            $group->money = $mode == 'paid' ? (int) $request->input('money') : 0;
        }

        DB::beginTransaction();

        try {
            $group->save();

            if ($avatar = $request->file('avatar')) {
                $group->storeAvatar($avatar);
            }

            $tags = collect($request->input('tags'))->map->id;

            if ($tags->count()) {
                $group->tags()->sync($tags);
            }

            $group->load(['user', 'tags', 'category', 'founder' => function ($query) {
                return $query->with('user');
            }]);

            if ($user->id === $group->founder->user_id) {
                $group->join_income_count = $group->incomes()->where('type', 1)->sum('amount');
                $group->pinned_income_count = $group->incomes()->where('type', 2)->sum('amount');
            }

            $group->joined = $group->members()->where('user_id', $user->id)->where('audit', 1)->first();

            DB::commit();

            return response()->json(['message' => '修改成功', 'group' => $group], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Join the group.
     *
     * @param \Illuminate\Http\Request $request
     * @param Zhiyi\PlusGroup\Models\Group $group
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function join(Request $request, GroupModel $group)
    {
        $user = $request->user();

        $member = $group->members()->where('user_id', $user->id)->first();

        if (! is_null($member) && in_array($member->audit, [0, 1])) {
            return response()->json(['message' => '已加入该圈子或加圈申请正在审核中'], 422);
        }

        if ($group->mode == 'paid' && ($user->wallet->balance < $group->money)) {
            return response()->json(['message' => '账户余额不足不能申请加入'], 422);
        }

        DB::beginTransaction();

        try {
            if ($group->mode == 'paid') {
                // 扣费账单
                $charge = new WalletChargeModel();
                $charge->user_id = $user->id;
                $charge->channel = 'user';
                $charge->action = 0;
                $charge->amount = $group->money;
                $charge->subject = '加圈扣费';
                $charge->body = sprintf('加入《%s》圈子扣费', $group->name);
                $charge->status = 1;
                $charge->account = $group->founder->user_id;
                $charge->save();
                // 申请扣费
                $user->wallet()->decrement('balance', $group->money);
            }

            $member = new GroupMemberModel();
            $member->user_id = $user->id;
            $member->audit = in_array($group->mode, ['paid', 'private']) ? 0 : 1;
            $member->role = 'member';
            $member->disabled = 0;
            $group->members()->save($member);

            if (in_array($group->mode, ['paid', 'private'])) {
                $log = new GroupMemberLogModel();
                $log->group_id = $group->id;
                $log->user_id = $user->id;
                $log->member_id = $member->id;
                $log->status = 0;
                $log->save();


                $message = sprintf('%s申请加入圈子%s', $user->name, $group->name);
            } else {
                $group->increment('users_count');

                $message = sprintf('%s加入了圈子%s', $user->name, $group->name);
            }
            $group->members()
                ->whereIn('role', ['administrator', 'founder'])
                ->where('audit', 1)
                ->get()
                ->map(function ($member) use ($group, $user, $message) {
                    // 旧版消息提醒
                    $member->user->unreadCount()->firstOrCreate([])->increment('unread_group_join_count', 1);
                    $member->user->sendNotifyMessage(
                        'group:join',
                        $message,
                        ['group' => $group, 'user' => $user]
                    );

                    // 新消息提醒, 非免费圈子需要给管理员发送未读数
                    if (in_array($group->mode, ['paid', 'private'])) {
                        $groups = $member->newQuery()
                            ->whereIn('role', ['administrator', 'founder'])
                            ->where('audit', 1)
                            ->where('user_id', $member->user_id)
                            ->get()
                            ->pluck('group_id');
                        $auditings = $member->newQuery()
                            ->whereIn('group_id', $groups)
                            ->where('audit', 0)
                            ->count();
                        $userCount = UserCount::firstOrNew([
                            'type' => 'user-group-join-pinned',
                            'user_id' => $member->user_id
                        ]);
                        // 解决事务提交前, 加圈申请未写入数据库导致数据量少1条的问题
                        $userCount->total = $auditings + 1;
                        $userCount->save();
                    }
                });

            DB::commit();
            return response()->json([
                'message' => in_array($group->mode, ['paid', 'private']) ? '申请已提交，等待管理员审核' : '加圈成功',
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * 退出圈子.
     *
     * @param Request $request
     * @param GroupModel $group
     * @return mixed
     */
    public function exitGroup(Request $request, GroupModel $group)
    {
        $user = $request->user();

        $member = $group->members()->where('user_id', $user->id)->first();

        if (is_null($member) || $member->audit !== 1) {
            return response()->json(['message' => '还未加入圈子或审核未通过'], 403);
        }

        if ($member->role === 'founder') {
            return response()->json(['message' => '你是圈子创始人，不能进行退圈'], 403);
        }

        $group->decrement('users_count', 1);
        $member->delete();

        $group->founder->user->sendNotifyMessage(
            'group:exit',
            sprintf('成员"%s"，已退出"%s"圈子', $user->name, $group->name),
            ['group' => $group, 'user' => $user]
        );

        return response()->json(null, 204);
    }

    /**
     * 全部圈子.
     *
     * @param Request $request
     * @return mixed
     */
    public function groups(Request $request)
    {
        $userId = $request->user('api')->id ?? 0;

        $keyword = $request->get('keyword');
        $limit = (int) $request->query('limit', 15);
        $offset = (int) $request->get('offset', 0);
        $categoryId = (int) $request->get('category_id');

        $builder = GroupModel::where('audit', 1);

        $groups = $builder->when($keyword, function ($query) use ($keyword) {
            return $query->where('name', 'like', sprintf('%%%s%%', $keyword));
        })
        ->when($categoryId, function ($query) use ($categoryId) {
            return $query->where('category_id', $categoryId);
        })
        ->offset($offset)
        ->limit($limit)
        ->get();

        $user_id = $request->user('api')->id ?? 0;

        $joined = GroupMemberModel::whereIn('group_id', $groups->map->id)
            ->where('user_id', $user_id)
            ->get();

        $groups = $groups->map(function (GroupModel $group) use ($joined) {
            $group->joined = null;
            $joined->each(function (GroupMemberModel $member) use ($group) {
                if ($member->group_id === $group->id && $member->audit === 1) {
                    $group->joined = $member;

                    return false;
                }
            });

            return $group;
        });

        return response()->json($groups, 200);
    }

    /**
     * Get a list of incomes.
     *
     * @param Request $request
     * @param GroupModel $group
     * @return mixed
     * @author BS <414606094@qq.com>
     */
    public function incomes(Request $request, GroupModel $group)
    {
        $user = $request->user();

        if ($user->id !== $group->founder->user_id) {
            return response()->json(['message' => ['无权查看']], 403);
        }

        $limit = $request->query('limit', 15);
        $after = $request->query('after', 0);
        $start = $request->query('start');
        $end = $request->query('end');

        $type = in_array($request->query('type'), ['all', 'join', 'pinned']) ? $request->query('type') : 'all';
        $incomes = $group->incomes()->where(function ($query) use ($type) {
            switch ($type) {
                case 'join':
                    return $query->where('type', 1);
                    break;
                case 'pinned':
                    return $query->where('type', 2);
                    break;
                default:
                    break;
            }
        })
        ->when($after, function ($query) use ($after) {
            return $query->where('id', '<', $after);
        })
        ->when($start, function ($query) use ($start) {
            return $query->where('created_at', '>=', Carbon::createFromTimestamp($start));
        })
        ->when($end, function ($query) use ($end) {
            return $query->where('created_at', '<', Carbon::createFromTimestamp($end));
        })
        ->limit($limit)
        ->orderBy('id', 'desc')
        ->with('user')
        ->get();

        return response()->json($incomes, 200);
    }

    /**
     * 圈子详情.
     *
     * @param Request $request
     * @param GroupModel $group
     * @return mixed
     * @author BS <414606094@qq.com>
     */
    public function show(Request $request, GroupModel $group)
    {
        $user_id = $request->user('api')->id ?? 0;

        $exist = in_array($group->mode, ['paid', 'private']);

        // 圈子被关闭或审核被驳回，无法访问
        if (in_array($group->audit, [2, 3])) {
            return response()->json(['message' => '圈子审核被驳回或已关闭无法访问'], 403);
        }

        $member = $group->members()->where('user_id', $user_id)->where('audit', 1)->first();

        // 私密和收费圈只有成员才能访问
        if ($exist && is_null($member)) {
            return response()->json(['message' => '未加入该圈子']);
        }

        if (! is_null($member) && $member->role === 'founder') {
            $group->join_income_count = (int) $group->incomes()->where('type', 1)->sum('amount');
            $group->pinned_income_count = (int) $group->incomes()->where('type', 2)->sum('amount');
        }

        $group->blacklist_count = $group->members()->where('disabled', 1)->count();

        $group->load(['user', 'tags', 'category', 'founder' => function ($query) {
            return $query->with('user');
        }]);

        $group->joined = $member;

        return response()->json($group, 200);
    }

    /**
     * 我的圈子.
     *
     * @param  Request $request
     * @param  Group   $group
     * @return mixed
     */
    public function userGroups(Request $request, GroupModel $group)
    {
        $user = $request->user();

        $type = $request->query('type');
        $limit = (int) $request->query('limit', 15);
        $offset = (int) $request->query('offset', 0);

        $type = in_array($type, ['join', 'audit', 'allow_post']) ? $type : 'join';

        $groups = $group->when($type, function ($query) use ($type, $user) {
            switch ($type) {
                case 'join':
                    return $query->whereHas('members', function ($query) use ($user) {
                        return $query->where('user_id', $user->id)->where('audit', 1);
                    })
                    ->where('audit', 1);
                    break;
                case 'audit':
                    return $query->where(function ($query) use ($user) {
                        return $query->where('user_id', $user->id)->where('audit', 0);
                    })->orWhere(function ($query) use ($user) {
                        return $query->whereHas('members', function ($query) use ($user) {
                            return $query->where('user_id', $user->id)->where('audit', 0);
                        });
                    });
                    break;
                case 'allow_post':
                    return $query->select('groups.*')
                    ->join('group_members', 'groups.id', '=', 'group_members.group_id')
                    ->where('groups.audit', 1)
                    ->where('group_members.user_id', $user->id)
                    ->where('group_members.disabled', 0)
                    ->where('group_members.audit', 1)
                    ->whereRaw('FIND_IN_SET(`group_members`.`role`, `groups`.`permissions`)');
                    break;
                default:
                    return $query->whereHas('members', function ($query) use ($user) {
                        return $query->where('user_id', $user->id)->where('audit', 1);
                    });
                    break;
            }
        })
        ->limit($limit)
        ->offset($offset)
        ->get();

        $joined = GroupMemberModel::whereIn('group_id', $groups->map->id)
            ->where('user_id', $user->id)
            ->get();

        $groups = $groups->map(function (GroupModel $group) use ($joined) {
            $group->joined = null;
            $joined->each(function (GroupMemberModel $member) use ($group) {
                if ($member->group_id === $group->id && $member->audit === 1) {
                    $group->joined = $member;

                    return false;
                }
            });

            return $group;
        });

        return response()->json($groups, 200);
    }

    /**
     * 获取他人的圈子列表.
     *
     * @param Request $request
     * @param GroupModel $groupModel
     * @return mixed
     * @author BS <414606094@qq.com>
     */
    public function othersGroups(Request $request, GroupModel $groupModel)
    {
        $user_id = $request->query('user_id', 0);

        $type = $request->query('type');
        $limit = (int) $request->query('limit', 15);
        $offset = (int) $request->query('offset', 0);

        $type = in_array($type, ['join']) ? $type : 'join';

        $groups = $groupModel->when($type, function ($query) use ($type, $user_id) {
            switch ($type) {
                case 'join':
                    return $query->whereHas('members', function ($query) use ($user_id) {
                        return $query->where('user_id', $user_id)->where('audit', 1);
                    });
                    break;
                default:
                    return $query->whereHas('members', function ($query) use ($user_id) {
                        return $query->where('user_id', $user_id)->where('audit', 1);
                    });
                    break;
            }
        })
        ->limit($limit)
        ->offset($offset)
        ->get();

        $joined = GroupMemberModel::whereIn('group_id', $groups->map->id)
            ->where('user_id', $user_id)
            ->get();

        $groups = $groups->map(function (GroupModel $group) use ($joined) {
            $group->joined = null;
            $joined->each(function (GroupMemberModel $member) use ($group) {
                if ($member->group_id === $group->id && $member->audit === 1) {
                    $group->joined = $member;

                    return false;
                }
            });

            return $group;
        });

        return response()->json($groups, 200);
    }

    /**
     * 设置圈子权限.
     */
    public function permissions(Request $request, GroupModel $group)
    {
        $user = $request->user();

        if ($group->founder->user_id !== $user->id) {
            return response()->json(['message' => '无权限操作'], 403);
        }

        $permissions = $request->input('permissions');

        if (! is_array($permissions) || ! count($permissions)) {
            return response()->json(['message' => '无效的参数'], 422);
        }

        $permissions = array_unique($permissions);

        foreach ($permissions as $permission) {
            if (! in_array($permission, ['member', 'administrator', 'founder'])) {
                return response()->json(['message' => '无效的参数'], 422);
            }
        }

        $group->permissions = implode(',', $permissions);
        $group->save();

        return response()->json(null, 204);
    }

    /**
     * 附近的圈子接口.
     *
     * @param Request $request
     * @param GroupModel $groupModel
     * @return mixed
     * @author BS <414606094@qq.com>
     */
    public function rounds(Request $request, GroupModel $groupModel)
    {
        $user_id = $request->user('api')->id ?? 0;
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $limit = (int) $request->query('limit', 15);
        $offset = (int) $request->query('offset', 0);

        $geohash = GeoHash::encode($latitude, $longitude);
        $geohash = substr($geohash, 0, 4); // 约20km

        $groups = GroupModel::where('audit', 1)
            ->where('geo_hash', 'like', $geohash.'%')
            ->limit($limit)
            ->offset($offset)
            ->orderBy('id', 'desc')
            ->get();

        $joined = GroupMemberModel::whereIn('group_id', $groups->map->id)
            ->where('user_id', $user_id)
            ->get();

        $groups = $groups->map(function (GroupModel $group) use ($joined) {
            $group->joined = null;
            $joined->each(function (GroupMemberModel $member) use ($group) {
                if ($member->group_id === $group->id && $member->audit === 1) {
                    $group->joined = $member;

                    return false;
                }
            });

            return $group;
        });

        return response()->json($groups, 200);
    }

    /**
     * 圈子数量统计.
     *
     * @return mixed
     */
    public function count()
    {
        $count = GroupModel::where('audit', 1)->count();

        return response()->json(['count' => $count], 200);
    }

    /**
     * 圈子协议.
     *
     * @param CommonConfigModel $configModel
     * @return mixed
     * @author BS <414606094@qq.com>
     */
    public function protocol(CommonConfigModel $configModel)
    {
        $protocol = $configModel->byNamespace('groups')
            ->byName('group:protocol')->value('value');

        return response()->json([
            'protocol' => $protocol ?? '',
        ], 200);
    }

    /**
     * 新版积分相关加入圈子接口.
     *
     * @param Request $request
     * @param GroupModel $group
     * @return mixed
     * @author BS <414606094@qq.com>
     */
    public function newJoin(Request $request, GroupModel $group)
    {
        $user = $request->user();

        $member = $group->members()->where('user_id', $user->id)->first();

        if (! is_null($member) && in_array($member->audit, [0, 1])) {
            return response()->json(['message' => '已加入该圈子或加圈申请正在审核中'], 422);
        }

        if ($group->mode == 'paid' && ($user->currency()->firstOrCreate(['type' => 1], ['sum' => 0])->sum < $group->money)) {
            return response()->json(['message' => '账户余额不足不能申请加入'], 422);
        }

        DB::beginTransaction();

        try {
            if ($group->mode == 'paid') {
                $process = new UserProcess();
                $process->prepayment($user->id, $group->money, $group->founder->user_id, '加圈扣费', sprintf('加入《%s》圈子扣费', $group->name));
            }

            $member = new GroupMemberModel();
            $member->user_id = $user->id;
            $member->audit = in_array($group->mode, ['paid', 'private']) ? 0 : 1;
            $member->role = 'member';
            $member->disabled = 0;
            $group->members()->save($member);

            if (in_array($group->mode, ['paid', 'private'])) {
                $log = new GroupMemberLogModel();
                $log->group_id = $group->id;
                $log->user_id = $user->id;
                $log->member_id = $member->id;
                $log->status = 0;
                $log->save();

                $message = sprintf('%s申请加入圈子%s', $user->name, $group->name);
            } else {
                $group->increment('users_count');

                $message = sprintf('%s加入了圈子%s', $user->name, $group->name);
            }

            $group->members()
                ->whereIn('role', ['administrator', 'founder'])
                ->where('audit', 1)
                ->get()
                ->map(function ($member) use ($message, $group, $user) {
                    // 旧版发送系统通知, 暂不删除
                    $member->user->unreadCount()->firstOrCreate([])->increment('unread_group_join_count', 1);
                    $member->user->sendNotifyMessage(
                        'group:join',
                        $message,
                        ['group' => $group, 'user' => $user]
                    );
                    // 新消息提醒, 非免费圈子需要给管理员发送未读数
                    if (in_array($group->mode, ['paid', 'private'])) {
                        $groups = $member->newQuery()
                            ->whereIn('role', ['administrator', 'founder'])
                            ->where('audit', 1)
                            ->where('user_id', $member->user_id)
                            ->get()
                            ->pluck('group_id');
                        $auditings = $member->newQuery()
                            ->whereIn('group_id', $groups)
                            ->where('audit', 0)
                            ->count();
                        $userCount = UserCount::firstOrNew([
                            'type' => 'user-group-join-pinned',
                            'user_id' => $member->user_id
                        ]);
                        // 解决事务提交前, 加圈申请未写入数据库导致数据量少1条的问题
                        $userCount->total = $auditings + 1;
                        $userCount->save();
                    }
                });

            DB::commit();

            return response()->json([
                'message' => in_array($group->mode, ['paid', 'private']) ? '申请成功，等待管理员审核' : '加圈成功',
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
