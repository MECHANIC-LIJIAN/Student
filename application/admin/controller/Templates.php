<?php

namespace app\admin\controller;

class Templates extends Base
{

    /**
     * 显示任务列表
     *
     * @return \think\Response
     */
    function list() {

        if (session('admin.is_super') == 1) {
            $templates = model('Templates')
                ->with('getUser')
                ->order(['status' => 'asc', 'update_time' => 'desc'])
                ->select();
        } else {
            $templates = model('Templates')
                ->where(['tuser' => session('admin.id')])
                ->with('getUser')
                ->order(['status' => 'asc', 'update_time' => 'desc'])
                ->select();
        }

        foreach ($templates as $value) {
            $value['shareUrl'] = url('index/Template/readTemplate', ['id' => $value['tid']], '', true);
            $value['tuser'] = $value['getUser']['username'];
        }
        $this->assign('templates', $templates);
        return view();
    }

    /**
     * 添加
     * @return \think\response\View
     */
    public function add()
    {

        return view();
    }

    /**
     * 删除
     * @return \think\response\View
     */
    public function del()
    {
        if (request()->isAjax()) {
            $tInfo = model('Templates')->with('options,datas')->find(input('post.id'));
            $result = $tInfo->together('options,datas')->delete();
            if ($result == 1) {
                $this->success('任务删除成功', 'admin/Templates/list');
            } else {
                $this->error("任务删除失败");
            }
        }
    }
    /**
     * 任务管理
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
