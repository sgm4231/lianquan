<?php

namespace Zhiyi\Plus\Models;

use Illuminate\Database\Eloquent\Model;

class UserCode extends Model
{
    public $table = 'user_codes';
    protected $primaryKey = 'id';

    public $timestamps = true;
    const DELETED_AT = 'deleted_at';
    const UPDATED_AT = 'updated_at';

}
