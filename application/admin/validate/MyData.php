<?php

namespace app\admin\validate;

use think\Validate;

class MyData extends Validate
{
    protected $rule =   [
        'dataName'  => 'require|max:25',
        'dataFile'=>'require|fileExt:xls,xlsx',
        'dataText'=>'require'
    ];
    
    protected $message  =   [
        'dataName.require' => '数据集名称必填',
        'dataName.max'     => '数据集名称最多不能超过25个字符',
        'dataFile.require' =>'请选择包含数据的文件',
        'dataText.require' =>'请输入数据集'
    ];

    public function sceneUpload()
    {
        return $this->only(['dataName','dataFile']);
    }

    public function sceneText()
    {
        return $this->only(['dataName','dataText']);
    }
}
