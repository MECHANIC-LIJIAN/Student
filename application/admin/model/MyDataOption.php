<?php

namespace app\admin\model;

use app\common\CommonModel;
use think\Model;
use think\model\concern\SoftDelete;

class MyDataOption extends CommonModel
{
    use SoftDelete;
}
