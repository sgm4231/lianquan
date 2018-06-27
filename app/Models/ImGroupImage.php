<?php

namespace Zhiyi\Plus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ImGroupImage extends Model
{
    use SoftDeletes;
    public $table = 'im_group_images';
    protected $dates = ['deleted_at'];

    public $timestamps = true;
    const DELETED_AT = 'deleted_at';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

}
