<?php


namespace app\index\model;

use think\Model;
use think\model\concern\SoftDelete;

class Templates extends Model
{
    use SoftDelete;

    public function options()
    {
        return $this->hasMany('TemplatesOption', 'tid', 'tid')->field('tid,sid,pid,title,rule');
    }

    
}
