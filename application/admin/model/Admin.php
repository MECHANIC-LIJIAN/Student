<?php

namespace app\admin\model;

use app\admin\business\Admin as BusinessAdmin;
use think\Db;
use think\Model;
use think\model\concern\SoftDelete;

class Admin extends Model
{
    //软删除
    use SoftDelete;
// 定义多种登录方式

    private $loginWay = [
        // 用户名
        'username',
        // 邮箱
        'email',
    ];

    //只读字段
    protected $readonly = ['email'];
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
        // 使用循环方式判断用户名是否存在
        foreach ($this->loginWay as $k => $v) {
            $result = $this->where($v, $data['username'])->field('id,username,password,salt,status,last_time')->find();
            // 如果存在就有这个用户，跳出
            if ($result) {
                break;
            }
        }

        $adminBusiness = new BusinessAdmin();
        $saltRes = $adminBusiness->passwordAddSalt($data['password'], $result['salt']);
        $data['password'] = $saltRes['password'];

        //判断用户是否存在
        if ($result) {
            //判断用户是否可用
            if ($result['status'] == 0) {
                return "用户不可用！";
            } elseif ($result['password'] == $data['password']) {
                //1 表示用户验证正确
                $sessionData = [
                    'id' => $result['id'],
                    'username' => $result['username'],
                    'last_time' => $result['last_time'],
                ];
                session('admin', $sessionData);
                $result->last_time = time();
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

        $adminBusiness = new BusinessAdmin();
        $saltRes = $adminBusiness->passwordAddSalt($data['password']);
        $data['password'] = $saltRes['password'];
        $data['salt'] = $saltRes['salt'];

        // 启动事务
        Db::startTrans();
        try {
            $result = $this->allowField(true)->save($data);
            $uid = $this->id;
            if ($result) {
                if (!empty($data['group_ids'])) {
                    $group = [];
                    foreach (explode(',', $data['group_ids']) as $k => $v) {
                        $group[] = ['uid' => $uid,
                            'group_id' => $v,
                        ];
                    }
                    // halt($group);
                    Db::name('AuthGroupAccess')->insertAll($group);
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // return $e->getMessage();
            return '添加失败！';
        }
        return 1;
    }

    /**
     * 修改管理员信息
     *
     * @param [type] $data
     * @return void
     */
    public function edit($data)
    {

        $validate = new \app\admin\validate\Admin();
        if (!$validate->scene('edit')->check($data)) {
            return $validate->getError();
        }

        $oldData = $this->field('password,salt')->getById($data['id']);

        if ($data['password'] !== $oldData['password']) {

            //如果密码改变，重新计算
            $adminBusiness = new BusinessAdmin();
            $saltRes = $adminBusiness->passwordAddSalt($data['password']);
            $data['password'] = $saltRes['password'];
            $data['salt'] = $saltRes['salt'];
        } else {
            $data['salt'] = $oldData['salt'];
        }

        // 启动事务
        Db::startTrans();
        try {
            $result = $this->where('id', $data['id'])
                ->update([
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'salt' => $data['salt'],
                    'email' => $data['email'],
                ]);

            Db::name('AuthGroupAccess')->where(['uid' => $data['id']])->delete();
            $uid = $data['id'];
            $group = [];
            foreach (explode(',', $data['group_ids']) as $k => $v) {
                $group[] = ['uid' => $uid,
                    'group_id' => $v,
                ];
            }
            // halt($group);
            Db::name('AuthGroupAccess')->insertAll($group);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // return $e->getMessage();
            return '修改失败！';
        }

        return 1;
    }

    //注册账户
    public function register($data)
    {
        $validate = new \app\admin\validate\Admin();
        if (!$validate->scene('register')->check($data)) {
            return $validate->getError();
        }

        $adminBusiness = new BusinessAdmin();
        $saltRes = $adminBusiness->passwordAddSalt($data['password']);
        $data['password'] = $saltRes['password'];
        $data['salt'] = $saltRes['salt'];

        // 启动事务
        Db::startTrans();
        try {
            $result = $this->allowField(true)->save($data);
            $uid = $this->id;
            if ($result) {
                if (!empty($data['group_ids'])) {
                    $group = [];
                    foreach (explode(',', $data['group_ids']) as $k => $v) {
                        $group[] = ['uid' => $uid,
                            'group_id' => $v,
                        ];
                    }
                    // halt($group);
                    Db::name('AuthGroupAccess')->insertAll($group);
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // return $e->getMessage();
            return '提交失败！';
        }
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
            $content .= "注册成功，你在本站注册的邮箱需要验证！请点击<a href='http://60.205.179.34:8080/admin/checkid/?emailkey=" . $data['emailkey'] . "&email=" . $data['email'] . "'>http://60.205.179.34:8080/admin/checkid/?emailkey=" . $data['emailkey'] . "&email=" . $data['email'] . "</a>(或者复制到浏览器打开)，完成验证！";
            $content .= '<p style="line-height: 30px">此邮件为系统自动发送，请勿直接回复！</p>';
            // $content .= '<p style="text-align: right;">团队</p>';
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
                    $user->where(array('email' => $get['email']))->delete();
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

                $adminBusiness = new BusinessAdmin();
                $saltRes = $adminBusiness->passwordAddSalt($data['password']);
                $data['password'] = $saltRes['password'];
                $data['salt'] = $saltRes['salt'];

                $adminInfo->password = $data['password'];
                $adminInfo->salt = $data['salt'];

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
