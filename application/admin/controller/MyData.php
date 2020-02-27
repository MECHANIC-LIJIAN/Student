<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class MyData extends Controller
{
    /**
     * 显示数据集列表
     *
     * @return \think\Response
     */
    public function index()
    {
        if (session('admin.is_super') == 1) {
            $dataSetList = model("MyData")
            ->order(['create_time' => 'desc'])
            ->field('id,uid,title,count')
            ->select();
        } else {
            $dataSetList = model("MyData")
            ->where(['uid' => session('admin.id')])
            ->order(['create_time' => 'desc'])
            ->field('id,uid,title,count')
            ->select();
        }
        $this->assign([
            'dataSetList' => $dataSetList,
        ]);
        return view();
    }

    /**
     * 显示创建数据集页面
     *
     * @return \think\Response
     */
    public function create()
    {
        return view();
    }

    /**
     * 通过文件新建数据集
     *
     * @return void
     */
    public function createByFile()
    {
        $dataInfo = [
            'uid' => session('admin.id'),
            'title' => input('post.dataName'),
            'dataFile' => input('file.dataFile'),
            'create_time' => time(),
        ];

        $result = model("MyData")->getDataByFile($dataInfo);

        if ($result == 1) {
            return $this->success("提交成功！", url('admin/MyData/index'));
        } else {
            return $this->error($result);
        }
    }

    /**
     * 通过文本新建数据集
     *
     * @return void
     */
    public function createByText()
    {
        $dataInfo = [
            'uid' => session('admin.id'),
            'title' => input('post.dataName'),
            'dataText' => input('post.dataText'),
            'create_time' => time(),
        ];

        $result = model("MyData")->getDataByText($dataInfo);

        if ($result == 1) {
            return $this->success("提交成功！", url('admin/MyData/index'));
        } else {
            return $this->error($result);
        }
    }

    /**
     * 数据集详情
     */
    public function read()
    {
        $id = input('id');
        $dataInfo = model('MyData')
            ->with('options')
            ->field('uid,title,id,count')
            ->find($id);

        $this->assign([
            'dataInfo' => $dataInfo,
        ]);
        return view();
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete()
    {
        if (request()->isAjax()) {
            $id = input('post.id');
            $dataInfo = model('MyData')
                ->with('options')
                ->field('id')
                ->where(['id' => $id])
                ->find();

            $result = $dataInfo->together('options')->delete();
            if ($result) {
                $this->success('删除成功', url('admin/MyData/index'));
            } else {
                $this->error('删除失败');
            }
        }
    }
}
