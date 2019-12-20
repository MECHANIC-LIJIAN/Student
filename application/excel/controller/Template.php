<?php


namespace app\excel\controller;

use think\Controller;

class Template extends Controller
{
    public function index($id)
    {
        $startTime = time(); //返回当前时间的Unix 时间戳
        $template=model("Templates")->with('getoption')->where(['id'=>$id])->find()->toArray();
        // dump($template['getoption']);
        $optionList=getOptionList($template['getoption'], $pid='opid', $id='id');
        // dump($optionList);
        // return $this->fetch('index', ['optionList'=>$optionList,'tname'=>$template['tname']]);
    }
}
