<?php

namespace app\admin\validate;

use think\Validate;

class MyData extends Validate
{
    protected $rule =   [
        'dataName'  => 'require|max:25',
        'dataFile'=>'fileExt:xls,xlsx',
        // 'dataSets'=>'require'
    ];
    
    protected $message  =   [
        'dataName.require' => '数据集名称必填',
        'dataName.max'     => '数据集名称最多不能超过25个字符',
        // 'dataSets.require'=>'请输入数据集'
    ];

    public function senceUpload()
    {
        return $this->only(['dataName,dataFile']);
    }

    // public function senceSave()
    // {
    //     return $this->only(['dataName,dataSets']);
    // }
}
