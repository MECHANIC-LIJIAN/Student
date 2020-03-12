<?php

namespace app\admin\controller;

class Templates extends Base
{

    /**
     * 显示表单列表
     *
     * @return \think\Response
     */
    function list() {

        $where = [];
        if (session('admin.is_super') != 1) {
            $where = ['uid' => session('admin.id')];
        }

        $templates = model('Templates')
            ->where($where)
            ->field('id,tid,uid,tname,count,myData,primaryKey,create_time,status')
            ->with('getUser,getMyData')
            ->order(['status' => 'asc', 'create_time' => 'desc'])
            ->select()
            ->toArray();

        $templateList=[];
        foreach ($templates as $value) {
            $tmp=$value;
            $tmp['shareUrl'] = url('index/Template/readTemplate', ['id' => $value['tid']], '', true);
            $tmp['username'] = $tmp['get_user']['username'];
            $tmp['mydata'] = $tmp['get_my_data']['title'];
            // $value['pcon'] = json_decode($value['options'], true)[$value['primaryKey']]['title'];
            $templateList[]=$tmp;
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

            $tInfo = model('Templates')->with('datas')->find(input('post.id'));
            
            $result = $tInfo->together('datas')->delete();
            
            if ($result == 1) {
                $this->success('表单删除成功', 'admin/Templates/list');
            } else {
                $this->error("表单删除失败");
            }
        }
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

            $tInfo = model('Templates')->find($id);

            if ($opt == "start") {
                $tInfo->status = 1;
            } else {
                $tInfo->status = 0;
            }

            $result = $tInfo->save();
            if ($result !== false) {
                $this->success('操作成功!', 'admin/Templates/list');
            } else {
                $this->error("操作失败!");
            }
        }
    }
}
