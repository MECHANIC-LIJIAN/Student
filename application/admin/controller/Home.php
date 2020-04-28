<?php

namespace app\admin\controller;

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
}
