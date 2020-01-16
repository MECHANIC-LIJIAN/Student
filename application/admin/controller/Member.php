<?php

namespace app\admin\controller;

class Member extends Base
{
    /**
     * 会员列表
     *
     * @return void
     */
    public function list()
    {
        if (request()->isAjax()) {
            $offset = input('post.offset');
            $limit = input('post.limit');
            $page = floor($offset / $limit) + 1;

            # 获取并且计算 页号 分页大小
            $list = model('Member')
                ->order('update_time', 'desc')
                ->page($page, $limit)
                ->select();
            # 查询相关数据
            $count = model('Member')->count();
            # 查询数据条数

            $res = [
                'total' => $count,
                'rows' => $list];
            # 构造返回数据类型

            # 返回JSON数据
            return $res;
        }
        return view();
    }

    /**
     * 会员添加
     *
     * @return void
     */
    public function add()
    {
        if (request()->isAjax()) {
            $data = [
                'username' => input('post.username'),
                'email' => input('post.email'),
                'password' => md5(input('post.password')),

            ];

            $result = model('Member')->add($data);
            if ($result == 1) {
                $this->success('会员添加成功', 'admin/Member/list');
            } else {
                $this->error($result);
            }
        }
        return view();
    }

    
    /**
     * 修改信息
     *
     * @return void
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $data = [
                'id' => input('post.id'),
                'username' => input('post.username'),
                'password' => md5(input('post.password')),
                'status' => input('post.status', 1),
            ];
            
            $result = model('Member')->edit($data);
            if ($result == 1) {
                $this->success('用户信息更新成功!', 'admin/Member/list');
            } else {
                $this->error($result);
            }
        }
        $memberInfo=model('Member')->find(input('id'));
        $viewData=[
            'memberInfo'=>$memberInfo
        ];
        $this->assign($viewData);
        return view();
    }

    /**
     * 删除用户
     *
     * @return void
     */
    public function del()
    {
        $cateInfo=model('Member')->with('comments,articles')->find(input('post.id'));
        $result=$cateInfo->together('comments,articles')->delete();
        if ($result == 1) {
            $this->success('用户删除成功!', 'admin/Member/list');
        } else {
            $this->error('用户删除失败');
        }
        
        return view();
    }
}
