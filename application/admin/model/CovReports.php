<?php

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class CovReports extends Model
{
    use SoftDelete;
    public function getProfile()
    {
        return $this->hasOne('Admin', 'id', 'uid')->field('id,username');
    }
}
