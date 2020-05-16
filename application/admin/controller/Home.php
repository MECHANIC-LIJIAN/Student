<?php

namespace app\admin\controller;
use think\Db;
class Home extends Base
{
    public function index()
    {
        return view();
    }

    public function help()
    {
        return view();
    }

    /**
     * 退出操作
     *
     * @return void
     */
    public function logout()
    {
        if (request()->isAjax()) {
            session(null);
            if (session('?admin')) {
                $this->error('退出失败');
            } else {
                $this->success('退出成功', 'admin/Index/login');
            }
        }
    }

    public function edit()
    {

        if (request()->isAjax()) {
            $data = [
                'id' => session('admin.id'),
                'username' => input('post.username'),
            ];
            // halt($data);
            $result = model('Admin')->update($data);
            if ($result == 1) {
                $res=Db::name('Admin')->field(['id,username,password,email,create_time'])->find($data['id']);
                $sessionData = [
                    'id' => $res['id'],
                    'username' => $res['username'],
                    'last_time' => $res['last_time'],
                ];
                session('admin', $sessionData);
                $this->success('信息编辑成功!', 'admin/Home/index');
            } else {
                $this->error($result);
            }
        }

        $uid =session('admin.id');

        $adminInfo = model('Admin')->field(['id,username,password,email,create_time'])->find($uid);

        $viewData = [
            'adminInfo' => $adminInfo,
        ];
        // halt($viewData);
        $this->assign($viewData);
        return view();
    }
}
