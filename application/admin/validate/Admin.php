<?php

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'username|管理员用户名' => 'require|unique:admin',
        'email|邮箱' => 'require|email|unique:admin',
        'password|密码' => 'require',
        'conpass|确认密码' => 'require|confirm:password',
       
    ];
    protected $message = [
        'email.unique' => '该邮箱已被注册，请更换！',
        'code.require' => '验证码不能为空',
        'conpass.confirm:password' => '两次输入的密码不一致',
    ];

    //登录场景验证
    public function sceneLogin()
    {
        return $this->only(['username', 'password'])->remove('username', 'unique');
    }

     //添加场景验证
     public function sceneAdd()
     {
         return $this->only(['username','password', 'conpass', 'email']);
     }

      //修改场景验证
      public function sceneEdit()
      {
          return $this->only(['username','password','email']);
      }

    //注册场景验证
    public function secneRegister()
    {
        return $this->only(['username', 'password', 'conpass', 'email']);
    }
    //重置密码场景验证
    public function sceneReset()
    {
        return $this->only(['code', 'password', 'conpass', 'email'])->remove('email', 'unique');
    }
}