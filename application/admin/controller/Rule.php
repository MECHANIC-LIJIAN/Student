<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\Admin;
use app\admin\validate\Rule as RuleValidate;
use think\Db;

/**
 * 后台权限管理
 */
class Rule extends Base
{

    //******************权限***********************
    /**
     * 权限列表
     */
    public function index()
    {
        $data = model('AuthRule')->field('id,title,name,pid,level,icon')->select()->toArray();
        $data = getTree($data);
        $assign = [
            'data' => $data,
        ];
        $this->assign($assign);
        return view();
    }

    /**
     * 添加权限
     */
    public function add()
    {
        $data = input('post.');
        unset($data['id']);
        $validate = new RuleValidate();
        $res = $validate->scene('add_rule')->check($data);
        if (!$res) {
            $this->error($validate->getError());
        }
        $result = model('AuthRule')->addData($data);
        if ($result) {
            $this->success('添加成功', url('Admin/Rule/index'));
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 修改权限
     */
    public function edit()
    {
        $data = input('post.');
        $map = array(
            'id' => $data['id'],
        );
        $validate = new RuleValidate();
        $res = $validate->scene('edit_rule')->check($data);
        if (!$res) {
            $this->error($validate->getError());
        }
        // halt($data);
        $result = model('AuthRule')->isUpdate(true)->editData($map, $data);
        if ($result) {
            $this->success('修改成功', url('Admin/Rule/index'));
        } else {
            $this->error('修改失败');
        }
    }

    /**
     * 删除权限
     */
    public function delete()
    {
        $id = input('post.id');
        $map = array(
            'id' => $id,
        );
        $result = model('AuthRule')->deleteData($map);
        if ($result) {
            $this->success('删除成功', url('Admin/Rule/index'));
        } else {
            $this->error('请先删除子权限');
        }
    }
    //*******************用户组**********************
    /**
     * 用户组列表
     */
    public function group()
    {
        $data = model('AuthGroup')->select();
        $assign = array(
            'data' => $data,
        );
        $this->assign($assign);
        return view();
    }

    /**
     * 添加用户组
     */
    public function add_group()
    {
        $data = input('post.');
        $result = model('AuthGroup')->addData($data);
        if ($result) {
            $this->success('添加成功', url('Admin/Rule/group'));
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 修改用户组
     */
    public function edit_group()
    {
        $data = input('post.');

        $map = array(
            'id' => $data['id'],
        );

        $result = model('AuthGroup')->editData($map, $data);
        if ($result) {
            $this->success('修改成功', url('Admin/Rule/group'));
        } else {
            $this->error('修改失败');
        }
    }

    /**
     * 删除用户组
     */
    public function delete_group()
    {
        $id = input('post.id', 0, 'intval');
        $map = array(
            'id' => $id,
        );
        $result = model('AuthGroup')->deleteData($map);
        if ($result) {
            $this->success('删除成功', url('Admin/Rule/group'));
        } else {
            $this->error('删除失败');
        }
    }

    //*****************权限-用户组*****************
    /**
     * 分配权限
     */
    public function rule_group()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $map = array(
                'id' => $data['id'],
            );
            $data['rules'] = implode(',', $data['rule_ids']);
            $result = model('AuthGroup')->editData($map, $data);
            if ($result) {
                $this->success('操作成功', url('Admin/Rule/group'));
            } else {
                $this->error('操作失败');
            }
        } else {
            $id = input('get.id');
            // 获取用户组数据
            $group_data = model('AuthGroup')->where(array('id' => $id))->find()->toArray();
            $group_data['rules'] = explode(',', $group_data['rules']);
            // 获取规则数据
            $rule_data = model('AuthRule')->field(['id,title,pid'])->select()->toArray();

            $rule_data = getTree($rule_data);
            $assign = array(
                'group_data' => $group_data,
                'rule_data' => $rule_data,
            );
            $this->assign($assign);
            return view();
        }
    }
    //******************用户-用户组*******************
    /**
     * 添加成员
     */
    public function check_user()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $user_data = model('Admin')->where(array('username' => $data['username']))->field(['id,username'])->find();

            if ($user_data == null) {
                $this->error('系统中无此用户');
            }

            unset($data['username']);
            $data['uid'] = $user_data['id'];
            $res = model('AuthGroupAccess')->where($data)->find();
            if ($res != null) {
                $user_data['res'] = 1;
            } else {
                $user_data['res'] = 0;
            }
            return [
                'code' => 1,
                'data' => $user_data,
            ];
        }
        $group_id = input('get.group_id');
        $group_name = model('AuthGroup')->getFieldById($group_id, 'title');
        $uids = model('AuthGroupAccess')->where(['group_id' => $group_id])->column('uid');

        $assign = array(
            'group_name' => $group_name,
            'uids' => $uids,
        );

        $this->assign($assign);
        return view();
    }

    /**
     * 添加用户到用户组
     */
    public function add_user_to_group()
    {
        $data = input('post.');
        $map = array(
            'uid' => $data['uid'],
            'group_id' => $data['group_id'],
        );
        $res = model('AuthGroupAccess')->where($map)->find();

        if ($res === null) {
            unset($data['username']);
            $res2 = Db::name('AuthGroupAccess')->insert($data);
        }
        if ($res2) {
            $this->success('操作成功', url('Admin/Rule/check_user', array('group_id' => $data['group_id'], 'username' => $data['username'])));
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 将用户移除用户组
     */
    public function delete_user_from_group()
    {
        $map = input('get.');
        $result = Db::name('AuthGroupAccess')->deleteData($map);
        if ($result) {
            $this->success('操作成功', url('Admin/Rule/admin_user_list'));
        } else {
            $this->error('操作失败');
        }
    }
}
