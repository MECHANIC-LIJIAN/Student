<?php

namespace app\admin\controller;

use think\Controller;

class Base extends Controller
{
    public function initialize()
    {
        // $exception_arth_list = [
        //     'admin/home/index',
        //     'admin/index/login',
        //     'admin/home/loginout',
        //     'admin/home/home',
        //     'admin/article/list',
        // ];
        // //获取到当前访问的页面
        // $module = request()->module(); //获取当前访问的模块
        // $controller = request()->controller(); //获取当前访问的控制器
        // $action = request()->action(); //获取当前访问的方法
        // $url = $module . '/' . $controller . '/' . $action; //转成字符串

        /**
         *验证是否登录
         */
        if (!session('?admin.id')) {
            $this->redirect('admin/Index/login');
        }

        $auth = new Auth();
        $userRow = session('admin');
        //验证权限
        if ($userRow['is_super'] =="1") {
            return view();
        }
        
        $rule_name = request()->module() . '/' . request()->controller() . '/' . request()->action();
        $this->uid = $userRow['id'];
        // $this->role_id = $userRow['role_id'];

        if ($rule_name == 'admin/login/index' || $rule_name == 'admin/index/index') {
            $result = true;
        } else {
            $result = $auth->check($rule_name, $this->uid);
        }
        if (!$result) {
            $this->error('您没有权限访问');
        }
    }
}
