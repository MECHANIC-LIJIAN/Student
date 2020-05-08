<?php

namespace app\admin\business;

class Admin
{
    public function passwordAddSalt($password,$salt=null)
    {
        if (!is_string($password)) {
            return "数据格式不正确";
        }

        if($salt==null){
            
            $salt = base64_encode(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        }
        $password = sha1($password . $salt);
        return [
            'password'=>$password,
            'salt'=>$salt
        ];
    }
}
