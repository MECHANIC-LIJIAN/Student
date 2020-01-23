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
        $templates = model('Templates')
            ->where(['tuser'=>session('admin.id')])
            ->order(['status' => 'asc','update_time'=>'desc'])
            ->select();
        foreach ($templates as $value) {
            $value['shareUrl'] = url('index/Template/readTemplate', ['id' => $value['tid']], '', true);
            $value['tuser']=session('admin.username');
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

    public function detail()
    {
        $tId = input('id');
        session('tId', $tId);
        $template = model('Templates')
            ->where(['tid' => $tId])
            ->field('tname')
            ->find();

        $fields = model('TemplatesOption')
            ->where(['tid' => $tId, 'type' => 'p'])
            ->field('sid,title')
            ->select();

        $templateField = [];
        foreach ($fields as $key => $value) {
            $value['field'] = $value['sid'];
            array_push($templateField, $value['sid']);
        }
        session('options', $templateField);
        
        $this->assign(['template' => $template, 'fields' => $fields]);
        return view();
    }

    public function dataList()
    {
        if (request()->isAjax()) {
            $offset = input('post.offset');
            $limit = input('post.limit');
            $page = floor($offset / $limit) + 1;

            # 获取并且计算 页号 分页大小
            $list = model('TemplatesData')
                ->where(['tid' => session('tId')])
                ->order(['update_time' => 'desc'])
                ->field(session('options'))
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
