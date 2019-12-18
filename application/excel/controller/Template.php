<?php


namespace app\excel\controller;

use think\Controller;

class Template extends Controller
{
    public function index($id)
    {
        $template=model("Templates")->with('getoption')->where(['id'=>$id])->find()->toArray();
        $optionList=getOptionList($template['getoption'], $pid='opid', $id='id');
        // dump($optionList);
        return $this->fetch('import/createTempalteSecond', ['optionList'=>$optionList,'tname'=>$template['tname']]);
    }
}
