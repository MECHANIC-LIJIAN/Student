<?php

namespace app\admin\validate;

use think\Validate;

class MyData extends Validate
{
    protected $rule =   [
        'title'  => 'require|max:25|unique:my_data,uid^title',
        'dataFile'=>'require|fileExt:xls,xlsx',
        'dataText'=>'require'
    ];
    
    protected $message  =   [
        'title.require' => '数据集名称必填',
        'title.max'     => '数据集名称最多不能超过25个字符',
        'title.unique'  => '已存在同名数据集',
        'dataFile.require' =>'请选择包含数据的文件',
        'dataText.require' =>'请输入数据集'
    ];

    public function sceneUpload()
    {
        return $this->only(['title','dataFile']);
    }

    public function sceneText()
    {
        return $this->only(['title','dataText']);
    }
}
