<?php

namespace app\admin\controller;

use think\Controller;

class Template extends Controller
{


    public function readTemplate()
    {
        $id = input('id');
        
        $template = model("Templates")->with('getoption')->where(['tid' => $id])->find()->toArray();
        
        $optionList = getOptionList($template['getoption'], $pid = 'pid', $id = 'sid');
        
        return $this->fetch('index', ['optionList' => $optionList, 'tname' => $template['tname']]);
    }
}
