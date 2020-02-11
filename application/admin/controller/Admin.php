<?php

namespace app\admin\controller;

class Admin extends Base
{
    /**
     * 管理员列表
     *
     * @return void
     */
    function list() {
        
        $admins = model('Admin')->order(['is_super' => 'asc', 'status' => 'desc'])->paginate(10);
        $this->assign('admins', $admins);
        return view();
    }

    /**
     * 添加管理员
     *
     * @return void
     */
    public function add()
    {
        if (request()->isAjax()) {
            $data = [
                'username' => input('post.username'),
                'password' => md5(input('post.password')),
                'conpass' => md5(input('post.conpass')),
                'email' => input('post.email'),
                'status' => 1,
                'last_time' => time(),
            ];
            $result = model('Admin')->add($data);
            if ($result == 1) {
                $this->success('添加成功！', 'admin/Admin/list');
            } else {
                $this->error($result);
            }
        }
        return view();
    }

    /**
     * 管理员状态操作
     */
    public function status()
    {
        $data = [
            'id' => input('post.id'),
            'status' => input('post.status') ? 0 : 1,
        ];
        $adminInfo = model('Admin')->find($data['id']);
        $adminInfo->status = $data['status'];
        $result = $adminInfo->save();
        if ($result) {
            $this->success('操作成功!', 'admin/Admin/list');
        } else {
            $this->error("操作失败!");
        }
    }

    /**
     * 编辑管理员信息
     *
     * @return void
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $data = [
                'id' => input('post.id'),
                'username' => input('post.username'),
                'password' => md5(input('post.password')),
                'conpass' => md5(input('post.conpass')),
                'email' => input('post.email'),
                'status' => input('post.status'),
            ];
            // dump($data);
            $result = model('Admin')->edit($data);
            if ($result == 1) {
                $this->success('管理员信息编辑成功!', 'admin/Admin/list');
            } else {
                $this->error($result);
            }
        }
        $adminInfo = model('Admin')->find(input('id'));
        if ($adminInfo) {
            $viewData = [
                'adminInfo' => $adminInfo,
            ];
            $this->assign($viewData);
            return view();
        }else {
            $this->error('用户不存在');
        }

    }

    /**
     * 删除管理员
     *
     * @return void
     */
    public function del()
    {
        dump(input('post.id'));
        $adminInfo = model('Admin')->find(input('post.id'));
        $result = $adminInfo->delete();
        if ($result == 1) {
            $this->success('管理员删除成功!', 'admin/Admin/list');
        } else {
            $this->error('管理员删除失败');
        }

        return view();
    }
}
