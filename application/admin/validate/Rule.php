<?php

namespace app\admin\validate;

use think\Validate;

class Rule extends Validate
{
    protected $rule = [
        'name|权限' => 'require|unique:auth_rule',
        'title|权限中文名' => 'require',
       
    ];
    protected $message = [
        
    ];


     //添加场景验证
     public function sceneAddRule()
     {
         return $this->only(['name','title']);
     }

      //修改场景验证
      public function sceneEditRule()
      {
          return $this->only(['name','title']);
      }

    //注册场景验证
    public function secneRegister()
    {
        return $this->only(['username', 'password', 'conpass', 'email']);
    }

}