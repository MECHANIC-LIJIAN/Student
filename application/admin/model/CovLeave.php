<?php

namespace app\admin\model;

use app\common\CommonModel;
use think\Model;
use think\model\concern\SoftDelete;

class CovLeave extends CommonModel
{
    use SoftDelete;
}
