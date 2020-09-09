<?php

namespace app\common;

use think\Model;
use think\model\concern\SoftDelete;

class CommonModel extends Model
{
    use SoftDelete;
    protected $defaultSoftDelete = 0;
}
