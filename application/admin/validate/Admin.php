<?php

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'username|用户名' => 'require|unique:admin|min:2|max:20|chsDash',
        'email|邮箱' => 'require|email|unique:admin',
        'password|密码' => 'require|min:8|max:30|alphaDash',
        'conpass|确认密码' => 'require|confirm:password',
       
    ];
    protected $message = [
        'email.unique' => '该邮箱已被注册，请更换！',
        'code.require' => '验证码不能为空',
        'password.alphaDash' => '密码只能包含字母、数字、下划线',
        'conpass.confirm:password' => '两次输入的密码不一致',
    ];

    //登录场景验证
    public function sceneLogin()
    {
        return $this->only(['username', 'password'])->remove('username', 'unique|chsDash');
    }

     //添加场景验证
     public function sceneAdd()
     {
         return $this->only(['username','password', 'conpass', 'email']);
     }

      //修改场景验证
      public function sceneEdit()
      {
          return $this->only(['username','password','email'])->remove('username', 'unique')->remove('email', 'unique')->remove('password', 'max');
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