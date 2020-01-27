<?php

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class Admin extends Model
{
    //软删除
    use SoftDelete;

    //只读字段
    protected $readonly=['email'];
    /**
     * 登录校验
     *
     * @param array $data
     * @return void
     */
    public function login($data)
    {
        $validate = new \app\admin\validate\Admin();
        if (!$validate->scene('login')->check($data)) {
            return $validate->getError();
        }
   
        $result = $this->where('username', $data['username'])->find();
      
        //判断用户是否存在
        if ($result) {
            //判断用户是否可用
            if ($result['status'] == 0) {
                return "用户不可用！";
            } elseif ($result['password']==$data['password']) {
                //1 表示用户验证正确
                $sessionData = [
                    'id' => $result['id'],
                    'username' => $result['username'],
                    'is_super' => $result['is_super'],
                    'last_time'=>$result['last_time'],
                ];
                session('admin', $sessionData);
                $result->last_time=time();
                $result->save();
                return 1;
            } else {
                return "用户名或者密码错误!";
            }
        } else {
            return "用户名不存在！";
        }
    }


    /**
     * 添加管理员
     *
     * @param [type] $data
     * @return void
     */
    public function add($data)
    {
        $validate = new \app\admin\validate\Admin();
        if (!$validate->scene('add')->check($data)) {
            return $validate->getError();
        }
        $result = $this->allowField(true)->save($data);
        if ($result) {
            return 1;
        } else {
            return '添加失败！';
        }
    }


    /**
     * 修改管理员信息
     *
     * @param [type] $data
     * @return void
     */
    public function edit($data)
    {
        // dump($data);
        $validate = new \app\admin\validate\Admin();
        if (!$validate->scene('edit')->check($data)) {
            return $validate->getError();
        }
        $result = $this->where('id', $data['id'])
        ->update([
            'username'=>$data['username'],
            'password'=>$data['password'],
            'email'=>$data['email']
        ]);
        if ($result !==false) {
            return 1;
        } else {
            return '修改失败！';
        }
    }
    
    //注册账户
    public function register($data)
    {
        $validate = new \app\admin\validate\Admin();
        if (!$validate->scene('register')->check($data)) {
            return $validate->getError();
        }
        $result = $this->allowField(true)->save($data);
        if ($result) {
            $user = new Admin();
            $keydate = time();
            $data['emailkey'] = md5($data['email']);
            $user->where(array('email' => $data['email']))->setField(array('email_key' => $data['emailkey'], 'datatime' => $keydate));
            // Content
            $emaildate = date('Y-m-d h:i:s', time());
            $content = '<html><head></head><body><div style="font-family:黑体;min-height:300px; background:#57bfaa;min-width:300px;max-width: 1000px;border: 0px solid #ccc; margin: auto;">';
            $content .= '<div style="width: 100%;font-size:20px;text-align: center;background: #4484c5; height: 50px;color: #FFF;line-height: 50px">邮件提醒</div>';
            $content .= '<div style="padding: 20px;color: #fff">';
            $content .= '<h3>尊敬的  ' . $data['username'] . '  你好：</h3>';
            $content .= '<p style="line-height: 30px">欢迎您在本网站注册</p>';
            $content .= "注册成功，你在本站注册的邮箱需要验证！请点击<a href='http://localhost/admin/checkid/?emailkey=" . $data['emailkey'] . "&email=" . $data['email'] . "'>http://localhost/admin/checkid/?emailkey=" . $data['emailkey'] . "&email=" . $data['email'] . "</a>(或者复制到浏览器打开)，完成验证！";
            $content .= '<p style="line-height: 30px">此邮件为系统自动发送，请勿直接回复！</p>';
            $content .= '<p style="text-align: right;">XXX团队</p>';
            $content .= '<p style="text-align: right;">' . $emaildate . '</p>';
            $content .= '</div>';
            $content .= '</div></body></html>';
            mailto($data['email'], $content);
            return 1;
        } else {
            return "注册失败！";
        }
    }



    //激活验证
    public function checkid($get)
    {
        $user = new Admin();
        $usersta = $user->where(array('email' => $get['email'], 'status' => 0))->select();
        // dump($usersta);
        if (is_null($usersta)) {
            return '你的账号已经激活，不需要再次激活!';
        } else {
            $result = $user->where(array('email' => $get['email'], 'email_key' => $get['emailkey']))->select();
            if ($result) {
                $keytime = $result[0]['datatime'];
                $presenttime = time();
                $agotime = ($presenttime - $keytime);
                if ($agotime > 3600) {
                    return "超过10分钟,链接失效";
                } else {
                    $result = $user->where(array('email' => $get['email'], 'email_key' => $get['emailkey']))->setField('status', '1');
                    return 1;
                }
            } else {
                return '激活失败重新激活';
            }
        }
    }

    

    //重置密码
    public function reset($data)
    {
        $validate = new \app\admin\validate\Admin();
        if (!$validate->scene('reset')->check($data)) {
            return $validate->getError();
        } else {
            $result = ($data['code'] == session('code'));
            if ($result == false) {
                return "验证码不正确";
            } else {
                $adminInfo = $this->where('email', $data['email'])->find();
                $password =$data['password'];
                $adminInfo->password = md5($password);
                $result = $adminInfo->save();
                if ($result) {
                    return 1;
                } else {
                    return '重置密码失败';
                }
            }
        }
    }
}
