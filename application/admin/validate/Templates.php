<?php

namespace app\admin\validate;

use think\Validate;

class Templates extends Validate
{
    protected $rule =   [
        'tname'  => 'require|max:25|unique:templates,uid^tname',
        'tFile'=>'require|fileExt:xls,xlsx'
    ];
    
    protected $message  =   [
        'tname.require' => '表单名称必填',
        'tname.max'     => '表单名称最多不能超过25个字符',
        'tname.unique'  => '已存在同名表单',
        'tFile'         => '请选择模板文件'
    ];

    public function sceneUpload()
    {
        return $this->only(['tname','tFile']);
    }
    public function sceneHand()
    {
        return $this->only(['tname']);
    }
}
