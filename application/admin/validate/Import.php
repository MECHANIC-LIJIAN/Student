<?php

namespace app\admin\validate;

use think\Validate;

class Import extends Validate
{
    protected $rule =   [
        'tname'  => 'require|max:25',
        'template'=>'fileExt:xls,xlsx'
    ];
    
    protected $message  =   [
        'tname.require' => '模板名称必填',
        'tname.max'     => '名称最多不能超过25个字符',
    ];

    public function sceneUpload()
    {
        return $this->only(['tname']);
    }
}
