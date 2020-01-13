<?php

namespace app\admin\controller;

use think\Controller;

class Home extends Base
{
    public function index()
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
        session(null);
        if (session('?admin.id')) {
            $this->error('退出失败');
        } else {
            $this->success('退出成功', 'admin/Index/login');
        }
    }
}
