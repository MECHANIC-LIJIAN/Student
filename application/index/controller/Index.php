<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $content=cookie('content');
        if ($content!=null&&$content!="") {
            $this->assign(['content'=>$content]);
        }
        cookie('content',null);
        return view();
    }
}
