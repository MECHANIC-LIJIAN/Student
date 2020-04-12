<?php

namespace app\index\controller;

use think\Controller;

class Checker
{
    public function checkSno()
    {
        if (request()->isAjax()) {
            $sno=input('post.sno');
            $info=model('Templates')->where(['tid'=>$sno])->find();
            if($info){
                return "true";
            }else {
                return "false";
            }
        }
    }
}
