<?php

namespace Zhiyi\Plus\Models;

use Illuminate\Database\Eloquent\Model;

class UserRelation extends Model
{
    public $table = 'user_relations';
    protected $primaryKey = 'id';

    public $timestamps = true;
    const DELETED_AT = 'deleted_at';
    const UPDATED_AT = 'updated_at';
}
