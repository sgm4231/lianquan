<?php

namespace Zhiyi\Plus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImStick extends Model
{
    use SoftDeletes;

    //...其他一些设置
    protected $dates = ['delete_at'];

    //指定表名

    protected $table = 'im_sticks';

    //指定主键

    protected $primaryKey = 'id';

    //是否开启时间戳

    public $timestamps = true;

    //设置时间戳格式为Unix

    protected $dateFormat = 'U';

}
