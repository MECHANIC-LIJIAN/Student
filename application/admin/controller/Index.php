<?php

namespace app\admin\controller;

use think\Controller;

class Index extends Controller
{
    public function initialize()
    {
        if (session('?admin.id')) {
            $this->redirect('admin/home/index');
        }
    }


    public function index()
    {
        return view();
    }

    
    //后台登录
    public function login()
    {
        if (request()->isAjax()) {
            $data = [
                'username' => input('post.username'),
                'password' => input('post.password'),
            ];

            $result = model('Admin')->login($data);
            if ($result == 1) {
                $this->success('登录成功！', 'admin/Home/index');
            } else {
                $this->error($result);
            }
        }
        return view();
    }

    //后台注册
    public function register()
    {
        if (request()->isAjax()) {
            $data = [
                'username' => input('post.username'),
                'password' =>  input('post.password'),
                'conpass' => input('post.conpass'),
                'email' => input('post.email'),
                'last_time' => time(),
                'group_ids'=>'8'//默认普通用户组
            ];
            
            $result = model('Admin')->register($data);
            if ($result == 1) {
                $this->success('注册成功,请到邮箱确认', 'admin/Index/login');
            } else {
                $this->error($result);
            }
        }
        return view();
    }

    //忘记密码,发送验证码
    public function forget()
    {
        if (request()->isAjax()) {
            $email = input('post.email');
            $admin = model("Admin");
            $adminInfo = $admin->where('email', $email)->find();
            if ($adminInfo) {
                $code = mt_rand(1000, 9999);
                session('code', $code);
                $result = mailto($email, $code);
                if ($result) {
                    $this->success("验证码已发送");
                } else {
                    $this->error("验证码发送失败");
                }
            } else {
                $this->error("用户不存在");
            }
        }
        return view();
    }

    //重置密码
    public function reset()
    {
        $data = [
            'code' => input('post.code'),
            'email' => input('post.email'),
            'password' => input('post.password'),
            'conpass' => input('post.conpass'),
        ];

        $result = model('Admin')->reset($data);
        if ($result == 1) {
            $this->success('密码重置成功', 'admin/Index/login');
        } else {
            $this->error($result);
        }
        return view();
    }

    //激活验证
    public function checkid()
    {
        $data['email'] = request()->param('email');
        $data['emailkey'] = request()->param('emailkey');
        $check = model('Admin')->checkid($data);
        if ($check == 1) {
            $this->success('激活成功', 'admin/Index/login');
        } else {
            $this->error($check);
        }
        return view();
    }
}
