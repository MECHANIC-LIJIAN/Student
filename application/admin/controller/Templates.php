<?php

namespace app\admin\controller;

use think\Controller;

class Templates extends Base
{

    
    /**
     * 显示文章列表
     *
     * @return \think\Response
     */
    public function list()
    {
        if (request()->isAjax()) {
            $offset=input('post.offset');
            $limit=input('post.limit');
            $page = floor($offset / $limit) + 1;
           
            # 获取并且计算 页号 分页大小
            $list = model('Templates')
            
            ->order(['status'=>'desc','update_time'=> 'desc'])
            ->page($page, $limit)
            ->select();
            # 查询相关数据
            $count = count($list);
            # 查询数据条数

            $res = [
            'total' => $count,
            'rows' => $list,
        ];
            # 返回JSON数据
            return $res;
        }

        $templates=model('Templates')->select();
        $this->assign('templates',$templates);
        return view();
    }

    /**
     * 删除
     * @return \think\response\View
     */
    public function del()
    {
        $articleInfo = model('Templates')->with('comments')->find(input('post.id'));
        $result=$articleInfo->together('comments')->delete();
        if ($result==1) {
            $this->success('任务删除成功', 'admin/Templates/list');
        } else {
            $this->error("任务删除失败");
        }
        
        return view();
    }

    /**
         * 文章推荐
         */
    public function top()
    {
        $data=[
            'id'=>input('post.id'),
            'is_top'=>input('post.is_top')?0:1
        ];

        $articleInfo=model('Article')->find($data['id']);
        $articleInfo->is_top=$data['is_top'];
        $result=$articleInfo->save();
        if ($result !==false) {
            $this->success('操作成功!', 'admin/Article/list');
        } else {
            $this->error("操作失败!");
        }
    }
}
