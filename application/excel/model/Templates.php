<?php


namespace app\excel\model;

use think\Model;
use think\model\concern\SoftDelete;

class Templates extends Model
{
    use SoftDelete;

    public function getoption()
    {
        return $this->hasMany('TemplatesOption', 'tid', 'id');
    }
}
