<?php

namespace app\admin\controller;

use think\auth\Auth;
use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;

class Base extends Controller
{

    public function initialize()
    {
        $exception_arth_list = [
            'admin/home/index',
            'admin/index/login',
            'admin/home/logout',
        ];
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

        // 获取auth实例
        $auth = new Auth();
        $userRow = Session::get('admin');

        //验证权限
        $request = Request::instance();
        $rule_name = $request->module() . '/' . $request->controller() . '/' . $request->action();
        $this->uid = $userRow['id'];

        if ($userRow['id'] > 10) {
            if (in_array(strtolower($rule_name), $exception_arth_list)) {
                $result = true;
            } else {
                $result = $auth->check($rule_name, $this->uid);
            }

            if (!$result) {
                $this->error('您没有权限访问', url('admin/index/login'));
            }
            
            if (request()->isGet()) {
                $groupIds = Db::name('AuthGroupAccess')->whereUid('=', $userRow['id'])->column('group_id');
                $this->groupIds=$groupIds;
                $ruleIds = Db::name('AuthGroup')->whereId('in', $groupIds)->column('rules');
    
                $ruleIds = explode(',', implode(',', $ruleIds));
                
                $list = Db::name('AuthRule')->whereId('in', $ruleIds)->field(['id,name,title,pid,icon'])->select();
                // dump($auth->getGroups($userRow['id']));
                $authList = getTree($list);
                View::share(['authList' => $authList]);
            }
            
        }

    }
}
