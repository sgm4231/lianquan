<?php

namespace Zhiyi\Plus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImNotice extends Model
{
    use SoftDeletes;

    //...其他一些设置
    protected $dates = ['delete_at'];

    //指定表名

    protected $table = 'im_notices';

    //指定主键

    protected $primaryKey = 'notice_id';

    //是否开启时间戳

    public $timestamps = true;

    //设置时间戳格式为Unix

    protected $dateFormat = 'U';

//    protected function getDateFormat(){
//        return time();
//    }

    //过滤字段，只有包含的字段才能被更新

//    protected $fillable = ['title','content'];

    //隐藏字段

//    protected $hidden = ['password'];
}
