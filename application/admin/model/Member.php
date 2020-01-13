<?php

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class Member extends Model
{
    //软删除
    use SoftDelete;

    /**
     * 关联评论
     *
     * @return void
     */
    public function comments()
    {
        return $this->hasMany('Comment', 'member_id', 'id');
    }
    public function articles()
    {
        return $this->hasMany('Article', 'member_id', 'id');
    }

   
  

    /**
     * 添加会员
     *
     * @return void
     */
    public function add($data)
    {
        $validate = new \app\common\validate\Member();
        if (!($validate->scene('add')->check($data))) {
            return $validate->getError();
        }
        $result = $this->allowField(true)->save($data);
        if ($result) {
            return 1;
        } else {
            return '会员添加失败';
        }
    }

    /**
     * 用户编辑
     */
    public function edit($data)
    {
        $validate = new \app\common\validate\Member();
        if (!($validate->scene('edit')->check($data))) {
            return $validate->getError();
        }
        $memberInfo=$this->where('id', $data['id'])->find();
        $memberInfo->username=$data['username'];
        $memberInfo->password=$data['password'];
        $memberInfo->status=$data['status'];

        $result =$memberInfo->save();
        // dump($data);
        if ($result !== false) {
            return 1;
        } else {
            return '更新失败';
        }
    }
}
