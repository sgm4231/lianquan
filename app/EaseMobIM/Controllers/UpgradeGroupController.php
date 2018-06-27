<?php
declare(strict_types=1);

namespace Zhiyi\Plus\EaseMobIm;

use GuzzleHttp\Client;
use Zhiyi\Plus\Models\ImGroupImageUser;
use Zhiyi\Plus\Models\ImStick;
use Zhiyi\Plus\Models\User;
use Illuminate\Http\Request;
use Zhiyi\Plus\Cdn\UrlManager;
use Zhiyi\Plus\Models\ImGroup;
use Zhiyi\Plus\Models\ImCluster;
use Zhiyi\Plus\Models\ImNotice;
use Zhiyi\Plus\Models\FileWith;
use Zhiyi\Plus\Models\ImGroupImage;

class UpgradeGroupController extends GroupController
{
    /**
     * 升级群
     * @param Request $request
     */
    public function upgradeGroup(Request $request){

        //升级的群id
        $im_group_id=$request->get('im_group_id');
        $type=$request->get('type');
        if(!$im_group_id){
            response()->json([
                'message' => ['不能没有群ID']
            ])->setStatusCode(500);
        }

        $res=ImCluster::where('id',$im_group_id)->select('grouplevel')->first()->toarray();
        if($res['grouplevel'] > 0){
            return response()->json([
                'message' => ['已经是最高等级群']
            ])->setStatusCode(500);
        }
        //支付环节暂时不做，支付成功后改变群等级状态
        //改变群等级以及群最大人数限定
        $res=$this->updatemaxusers($im_group_id,$type);

        if($res->original['maxusers']==2000){
            return response()->json([
                'message' => ['升级成功']
            ],200);
        }else{
            return response()->json([
                'message' => ['升级失败']
            ],500);
        }
    }

    public function updatemaxusers($im_group_id,$type)
    {

        $callback = function () use ($im_group_id,$type) {

            //升级群为2000人群
            $options['maxusers'] = 2000;
            $url = $this->url.'chatgroups/'.$im_group_id;
            $data['headers'] = [
                'Authorization' => $this->getToken(),
            ];
            $data['body'] = json_encode($options);
            $data['http_errors'] = false;
            $Client = new Client();
            $result = $Client->request('put', $url, $data);

            if ($result->getStatusCode() != 200) {
                $error = $result->getBody()->getContents();

                return response()->json([
                    'message' => [
                        json_decode($error)->error_description,
                    ],
                ])->setStatusCode(500);
            }
            //同步环信群组信息到数据表
            $this->synchronousGroupInformation($im_group_id,'1',$type);

            return response()->json([
                'message' => ['成功'],
                'maxusers' => $options['maxusers'],
            ])->setStatusCode(200);

        };

       return $this->getConfig($callback);
    }

    /**
     * 随机推荐5个群
     * @param ImCluster $imCluster
     * @return \Illuminate\Http\JsonResponse
     */
    public function randomquery(ImCluster $imCluster){
        $data = $imCluster
            ->leftJoin('im_group', 'im_clusters.id', '=', 'im_group.im_group_id')
            ->select('im_clusters.*', 'im_group.group_face')
            ->orderBy(\DB::raw('RAND()'))
            ->take(5)
            ->get()
            ->toArray();
        return response()->json($data);
    }

}
