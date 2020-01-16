<?php

namespace app\admin\controller;

class Index extends Base
{
    /**
     * 重复登录过滤
     */
    public function initialize()
    {
        if (session('?admin.id')) {
            $this->redirect('admin/home/index');
        }
    }
    //后台登录
    public function login()
    {
        if (request()->isAjax()) {
            $data = [
                'username' => input('post.username'),
                'password' => md5(input('post.password')),
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

    //后台注册
    public function register()
    {
        if (request()->isAjax()) {
            $data = [
                'username' => input('post.username'),
                'password' => md5(input('post.password')),
                'conpass' => md5(input('post.conpass')),
                'email' => input('post.email'),
            ];
            $result = model('Admin')->register($data);
            if ($result == 1) {
                $this->success('注册成功', 'admin/Index/login');
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
