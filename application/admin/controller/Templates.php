<?php

namespace app\admin\controller;

use myredis\Redis;

class Templates extends Base
{

    /**
     * 显示表单列表
     *
     * @return \think\Response
     */
    function list() {

        $where = [];
        $field = 'id,tid,uid,ttype,tname,myData,primaryKey,create_time,status';

        if (!in_array(2, $this->groupIds)) {
            $where = ['uid' => session('admin.id')];
        } else {
            $this->assign('display', 'display');
        }

        $templates = model('Templates')
            ->where($where)
            ->field($field)
            // ->withCount('datas')
            ->with('getUser,getMyData')
            ->order(['status' => 'asc', 'create_time' => 'desc'])
            ->select()
            ->toArray();
        // dump($templates);
        $templateList = [];

        foreach ($templates as $value) {
            $tmp = $value;
            $tmp['shareUrl'] = url('index/Template/readTemplate', ['id' => $value['tid']], '', true);
            $tmp['username'] = $tmp['get_user']['username'];
            if ($value['myData'] != null || $value['myData'] !== '') {
                $tmp['mydata'] = $tmp['get_my_data']['title'];
            }
            // $value['pcon'] = json_decode($value['options'], true)[$value['primaryKey']]['title'];
            $templateList[] = $tmp;
        }

        $this->assign('templates', $templateList);
        return view();
    }

    /**
     * 添加表单
     * @return \think\response\View
     */
    public function add()
    {
        $myData = model("MyData")
            ->where(['uid' => session('admin.id')])
            ->field('id,title')
            ->select();
        session('myData', $myData);
        return view();
    }

    /**
     * 删除表单
     * @return \think\response\View
     */
    public function del()
    {
        if (request()->isAjax()) {

            $tInfo = model('Templates')->with('datas')->field('id,tid')->find(input('post.id'));

            $result = $tInfo->together('datas')->delete();
            $redisKey = 'template_' . $tInfo['tid'];
            $redis = new Redis();
            $redis->del($redisKey);
            if ($result == 1) {
                $this->success('表单删除成功', 'admin/Templates/list');
            } else {
                $this->error("表单删除失败");
            }
        }
    }

    /**
     * 表单编辑
     *
     * @return void
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $params = input('post.');
            $tInfo = [];

            if ((!array_key_exists('option_1', $params))) {
                $this->error("请至少添加一个字段");
            }
            $tInfo = [
                'tid' => $params['tid'],
                'tname' => $params['templateName'],
                'remarks' => $params['remarks'],
                'primaryKey' => $params['primaryKey'],
                'myData' => $params['myData'],
            ];

            unset($params['templateName']);
            unset($params['primaryKey']);
            unset($params['myData']);
            unset($params['remarks']);
            unset($params['tid']);

            $tInfo['params'] = $params;

            $res = model('Templates')->editByHand($tInfo);
            if ($res == 1) {
                $redis = new Redis();
                $redis->del('template_' . $tInfo['tid']);
                $this->success("表单编辑成功", 'admin/Templates/list');
            } else {
                $this->error($res);
            }
        }

        $id = input('tid');
        $tInfo = model('Templates')->where(['tid' => $id])->find();
        $tInfo['options'] = json_decode($tInfo['options'], true);

        $keys = [];
        foreach ($tInfo['options'] as $k => $v) {
            if (!isset($v['options']) && $v['rule'] != 'text') {
                $keys[$k] = $v['title'];
            }
        }
        #获取显示在页面的数据列表
        $this->assign([
            'optionList' => $tInfo['options'],
            'tInfo' => $tInfo,
            'keys' => $keys,
            'formLength' => sizeof($tInfo['options']) + 1,
        ]);
        return view();
    }

    /**
     * 表单管理
     *
     * @return void
     */
    public function control()
    {

        if (request()->isAjax()) {
            $id = input('post.id');
            $opt = input('post.opt');

            $tInfo = model('Templates')->field('id,tid,status,endTime')->find($id);

            if ($opt == "start") {
                if($tInfo['endTime']<=time()){
                    $tInfo->endTime = 0;
                }
                $tInfo->status = 1;
            } else {
                $tInfo->status = 0;
            }

            $result = $tInfo->save();
            if ($result !== false) {
                $redis = new Redis();
                $redis->del('template_' . $tInfo['tid']);
                $this->success('操作成功!', 'admin/Templates/list');
            } else {
                $this->error("操作失败!");
            }
        }
    }
}
