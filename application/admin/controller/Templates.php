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
        return view();
    }

    /**
     * 添加文章
     *
     * @return \think\Response
     */
    public function add()
    {
        if (request()->isAjax()) {
            $data=[
                'title'=>input('post.title'),
                'tags'=>input('post.tags'),
                'is_top'=>input('post.is_top', 0),
                'cateid'=>input('post.cateid'),
                'desc'=>input('post.desc'),
                'content'=>input('post.content'),
                'pic'=>input('post.pic'),
                'author'=>input('post.author')
            ];
            
            $result=model('Article')->add($data);
            if ($result==1) {
                $this->success('文章添加成功', 'admin/Article/list');
            } else {
                $this->error($result);
            }
        }
        $cates=model('Cate')->select();
        $viewData=[
            'cates'=>$cates
        ];
        $this->assign($viewData);
        return view();
    }


    /**
     * 编辑文章
     * @return \think\response\View
     */
    public function edit()
    {
        $articleInfo = model('Article')->find(input('id'));
        $cates = model('Cate')->select();
        $data = [
                'articleInfo' => $articleInfo,
                'cates' => $cates
            ];
        // dump($data);
        $this->assign($data);


        if (request()->isAjax()) {
            $data=[
                'id'=>input('post.id'),
                'title'=>input('post.title'),
                'tags'=>input('post.tags'),
                'is_top'=>input('post.is_top', 0),
                'cateid'=>input('post.cateid'),
                'desc'=>input('post.desc'),
                'content'=>input('post.contentback'),
            ];
           
            $result=model('Article')->edit($data);
            if ($result==1) {
                $this->success('文章修改成功', 'admin/Article/list');
            } else {
                $this->error($result);
            }
        }
        return view();
    }


    /**
     * 删除文章
     * @return \think\response\View
     */
    public function del()
    {
        $articleInfo = model('Article')->with('comments')->find(input('post.id'));
        $result=$articleInfo->together('comments')->delete();
        if ($result==1) {
            $this->success('文章删除成功', 'admin/Article/list');
        } else {
            $this->error("文章删除失败");
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


    /**
    * 上传图片
    *
    * @return void
    */
    public function upload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');

        // 移动到框架应用根目录/uploads/ 目录下
            $info = $file->move('/var/www/html/public/uploads');        //在public/创建uploads,获取图片的地址
          
        if ($info) {
            $url = "/uploads/" . $info->getSaveName();            //在域名加图片的地址进行拼接
            $datas = ["errno" => 0, "data" => [$url]];
            return json($datas);
        } else {
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }
}
