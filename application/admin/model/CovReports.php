<?php

namespace app\admin\model;

use app\common\CommonModel;
use think\Model;
use think\model\concern\SoftDelete;

class CovReports extends CommonModel
{
    use SoftDelete;
    public function getProfile()
    {
        return $this->hasOne('Admin', 'id', 'uid')->field('id,username')->bind('username');
    }
}
