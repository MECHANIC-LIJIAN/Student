<?php

namespace app\admin\controller;

use think\auth\Auth;
use think\Db;

class Admin extends Base
{

    /**
     * 管理员列表
     *
     * @return void
     */
    function list() {
        $data = input("get.");

        $admins = model('Admin')
            ->order(['status' => 'desc', 'id' => 'asc'])
            ->field('id,username,email,status');
        if ($data['username'] == "") {
            $admins = $admins->paginate(15, false);
        } else {
            $admins = $admins
                ->where('username', 'like', "%" . $data['username'] . "%")
                ->paginate(15, false, ['query' => request()->param()]);
        }

        $this->assign([
            'admins' => $admins,
        ]);
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
                'password' => input('post.password'),
                'conpass' => input('post.conpass'),
                'email' => input('post.email'),
                'group_ids' => input('post.group_ids'),
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
        $groups = model('AuthGroup')->select();
        $assign = array(
            'groups' => $groups,
        );

        $this->assign($assign);
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
                'password' => input('post.password'),
                'email' => input('post.email'),
                'group_ids' => input('post.group_ids'),
            ];
            // halt($data);
            $result = model('Admin')->edit($data);
            if ($result == 1) {
                $this->success('管理员信息编辑成功!', 'admin/Admin/list');
            } else {
                $this->error($result);
            }
        }

        $uid = input('id');

        $auth = new Auth();
        $adminInfo = model('Admin')->field(['id,username,password,email,create_time'])->find($uid);

        $allGroups = Db::name('AuthGroup')->field(['id,title'])->select();

        $groups = Db::name('AuthGroupAccess')->where(['uid' => $uid])->column('group_id');

        // halt($auth->getGroups($uid));
        if ($adminInfo) {
            $viewData = [
                'adminInfo' => $adminInfo,
                'groups' => $groups,
                'all_group' => $allGroups,
            ];
            // halt($groups);
            $this->assign($viewData);
            return view();
        } else {
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
