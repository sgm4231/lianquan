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

namespace Zhiyi\Plus\Http\Controllers\APIs\V2;

use RuntimeException;
use Tymon\JWTAuth\JWTAuth;
use Zhiyi\Plus\Models\User;
use Illuminate\Http\Request;
use Zhiyi\Plus\Models\UserCode;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class UserCodeController extends Controller
{

    /**
     * 获取当前用户的推荐码和二维码数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserCodeData(Request $request)
    {
        //接口需求，带参数的二维码、推荐码
        //开发思路：需要二维码生成
        $user_id = $request->user()->id;  //当前登录用户

        //查询当前用户是否有推荐码
        $code_data = UserCode::where('user_id',$user_id)->get()->first();
        if(empty($code_data)){
            //空，就去给生成一个推荐码
            $new_code = $this->getRandomString(6);
            $content = new UserCode();
            $content->user_id = $user_id;
            $content->user_code = $new_code;
            if( $content->save()){
                //保存成功
                $my_code =$new_code;
            }

        }else{
            //获取推荐码返回前端
            $my_code =$code_data->user_code;
        }

        //生成二维码，带上推荐码
        $user_url =url('/api/v2/shareRegister?user_code='.$my_code);
        $data = array('user_code'=>$my_code,'user_url'=>$user_url);
        return response()->json($data, 200);
    }



    /**
     * 获取推荐码二维码图片
     * @param Request $request
     */
    public function getCodeImages( Request $request){
        $user_code = $request->get('user_code');
        $url =url('/api/v2/shareRegister?user_code='.$user_code);
        //输出带着参数的URL二维码
        echo QrCode::encoding('UTF-8')->size(200)->generate($url);
    }

    /**
     * 会员通过分析二维码页面进入的注册页面
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function shareRegister( Request $request){
        //访问拿到user_code 推荐码
        $user_code = $request->get('user_code');
        if($request->has('user_code')){
            //识别user_code，判断这个code 是否有效
            $sql_code_arr = UserCode::pluck('user_code')->toArray();
            if(!in_array($user_code,$sql_code_arr)){
                //user_code 不存在
                //echo '不存在';exit();
                $user_code = null;
            }

        }else{
            // echo '没有拿到参数';
            $user_code = null;
        }

        $res = array('user_code'=>$user_code);
        //开始进入分享注册页面
        return view('moblie.register',$res);
//        return view('welcome');
    }

    /**
     * 为用户生成唯一推荐码
     * @return string
     */
    public  function getRandomString($length)
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $key = '';
        for($i=0;$i<$length;$i++)
        {
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }

        $num = UserCode::where('user_code',$key)->count();
        if($num == 0){
            return $key;
        }else{
            $key = $this->getRandomString($length);
            return $key;
        }

    }

    /**
     * 创建用户.
     *
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function store(StoreUserPost $request, ResponseFactoryContract $response, JWTAuth $auth)
    {
        $phone = $request->input('phone');
        $email = $request->input('email');
        $name = $request->input('name');
        $password = $request->input('password');
        $channel = $request->input('verifiable_type');
        $code = $request->input('verifiable_code');
        $role = CommonConfig::byNamespace('user')
            ->byName('default_role')
            ->firstOr(function () {
                throw new RuntimeException('Failed to get the defined user group.');
            });

        $verify = VerificationCode::where('account', $channel == 'mail' ? $email : $phone)
            ->where('channel', $channel)
            ->where('code', $code)
            ->orderby('id', 'desc')
            ->first();

        if (! $verify) {
            return $response->json(['message' => ['验证码错误或者已失效']], 422);
        }

        $user = new User();
        $user->phone = $phone;
        $user->email = $email;
        $user->name = $name;
        $user->createPassword($password);

        $verify->delete();
        if (! $user->save()) {
            return $response->json(['message' => '注册失败'], 500);
        }

        $user->roles()->sync($role->value);

        return $response->json([
            'token' => $auth->fromUser($user),
            'ttl' => config('jwt.ttl'),
            'refresh_ttl' => config('jwt.refresh_ttl'),
        ])->setStatusCode(201);
    }

    /**
     * app下载
     * @param Request $request
     */
    public function appDownload(Request $request){

        return view('moblie.download');
    }

}
