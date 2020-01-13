<?php


namespace app\admin\controller;

use think\Controller;

class Template extends Controller
{
    public function readTemplate()
    {
        $id=input('id');
        $startTime = time(); //返回当前时间的Unix 时间戳
        $template=model("Templates")->with('getoption')->where(['tid'=>$id])->find()->toArray();
        // dump($template['getoption']);
        $optionList=getOptionList($template['getoption'], $pid='pid', $id='sid');
        // dump($optionList);
        return $this->fetch('index', ['optionList'=>$optionList,'tname'=>$template['tname']]);
    }
}
