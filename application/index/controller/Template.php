<?php

namespace app\index\controller;

use think\Controller;

class Template extends Controller
{

    public function readTemplate()
    {
        $id = input('id');
        cookie('tId',$id);
        $template = model("Templates")->with('options')->where(['tid' => $id])->find()->toArray();
        
        $optionList = getOptionList($template['options'], $pid = 'pid', $id = 'sid');

        $templateField=[];
        foreach ($template['options'] as $value) {
            if ($value['pid']=="0") {
                array_push($templateField,$value['sid']);
            }
        }
        
        cookie('options',$templateField);
        
        return $this->fetch('index', ['optionList' => $optionList, 'tname' => $template['tname'],]);
    }

    public function collect()
    {
        if (request()->isAjax()) {
            $templateField=cookie('options');
            $data=[];
            foreach ($templateField as $key => $value) {
                $data[$value]=input("post.$value");
            }
            $data['tid']=cookie('tId');
            $res=model('TemplatesData')->allowField(true)->save($data);
            if ($res) {
                $this->success('提交成功！',url('index/index/index'));
            }else {
                $this->error('提交失败！');
            }
        }
        return view();
    }
}
