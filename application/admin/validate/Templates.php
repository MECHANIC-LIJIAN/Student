<?php

namespace app\admin\validate;

use think\Validate;

class Templates extends Validate
{
    protected $rule =   [
        'tName'  => 'require|max:25',
        'tFile'=>'require|fileExt:xls,xlsx'
    ];
    
    protected $message  =   [
        'tName.require' => '表单名称必填',
        'tName.max'     => '表单名称最多不能超过25个字符',
        'tFile'         => '请选择模板文件'
    ];

    public function sceneUpload()
    {
        return $this->only(['tName','tFile']);
    }
}
