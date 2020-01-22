<?php

namespace app\admin\controller;
 
use think\Session;
use think\Request;
use think\Loader;
use think\Db;
use think\auth\Auth;

class Manage extends Base
{
    
 
    /**
     * 权限列表
     */
    public function auth()
    {
        $data = Db::name('auth_rule')->select();
        $this->assign(['data' => $data]);
        return $this->fetch();
    }
 
 
    /**
     * 添加权限
     */
    public function addAuth()
    {
        $data=input('post.');
//        var_dump($data);
        unset($data['id']);
        $result=Db::name('auth_rule')->insert($data);
        if ($result) {
            $this->success('添加成功', 'admin/Manage/auth');
        } else {
            $this->error('添加失败');
        }
    }
 
    /**
     * 修改权限
     */
    public function editAuth()
    {
        $data=input('post.');
        $info=['title'=>$data['title'],'name'=>$data['name']];
        $result=Db::name('auth_rule')->where(["id"=>$data['id']])->update($info);
        
        if ($result) {
            $this->success('修改成功!', 'admin/Manage/auth');
        } else {
            $this->error('您没有做任何修改!');
        }
    }
 
    /**
     * 删除权限
     */
    public function deleteAuth($id)
    {
        $map=array(
            'id'=>$id
        );
        $result=Db::name('auth_rule')->delete($map);
        if ($result) {
            $this->success('删除成功', 'admin/Manage/auth');
        } else {
            $this->error('请先删除子权限');
        }
    }
}