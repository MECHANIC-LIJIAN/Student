<?php


namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class Templates extends Model
{
    use SoftDelete;

    public function options()
    {
        return $this->hasMany('TemplatesOption', 'tid', 'tid');
    }
    public function datas()
    {
        return $this->hasMany('TemplatesData', 'tid', 'tid');
    }
    public function getUser()
    {
        return $this->belongsTo('Admin', 'tuser', 'id');
    }
}
