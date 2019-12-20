<?php

namespace app\excel\validate;

use think\Validate;

class Import extends Validate
{
    protected $rule =   [
        'tname'  => 'require|max:25'
    ];
    
    protected $message  =   [
        'tname.require' => '模板名称必填',
        'tname.max'     => '名称最多不能超过25个字符',
    ];

    public function senceUpload()
    {
        return $this->only(['tname']);
    }
}
