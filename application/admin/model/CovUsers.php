<?php

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class CovUsers extends BaseModel
{
    use SoftDelete;
    public function getProfile()
    {
        return $this->hasOne('Admin','id','uid')->field('id,username');
    }

}
