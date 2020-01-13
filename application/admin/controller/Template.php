<?php

namespace app\admin\controller;

use think\Controller;

class Template extends Controller
{

    public function detail()
    {
        $tId = input('id');
        session('tId', $tId);
        $template=model('Templates')
        ->where(['tid' => $tId])
        ->find();
        $templateField = model('TemplatesOption')
        ->where(['tid' => $tId, 'type' => 'p'])
        ->field('sid,title')
        ->select();

        foreach ($templateField as $key => $value) {
            $value['field'] = $value['sid'];
        }
        $this->assign(['template'=>$template,'fields'=> $templateField]);
        return view();
    }

    public function dataList()
    {
            if (request()->isAjax()) {
            $offset=input('post.offset');
            $limit=input('post.limit');
            $page = floor($offset / $limit) + 1;
           
            # 获取并且计算 页号 分页大小
            $list = model('TemplatesData')
            ->where(['tid' => session('tId')])
            ->order(['update_time'=> 'desc'])
            ->page($page, $limit)
            ->select();
            # 查询相关数据
            $count = count($list);
            # 查询数据条数

            $res = [
            'total' => $count,
            'rows' => $list,
        ];
            # 返回JSON数据
            return $res;
        }
    }
    public function readTemplate()
    {
        $id = input('id');
        $startTime = time(); //返回当前时间的Unix 时间戳
        $template = model("Templates")->with('getoption')->where(['tid' => $id])->find()->toArray();
        // dump($template['getoption']);
        $optionList = getOptionList($template['getoption'], $pid = 'pid', $id = 'sid');
        // dump($optionList);
        return $this->fetch('index', ['optionList' => $optionList, 'tname' => $template['tname']]);
    }
}
