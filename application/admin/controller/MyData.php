<?php

namespace app\admin\controller;

use myredis\Redis;

class MyData extends Base
{
    /**
     * 显示数据集列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = [];
        $field = 'id,uid,title,count,create_time';

        if (!in_array(2, $this->groupIds)) {
            $where = ['uid' => session('admin.id')];
        } else {
            $this->assign('display', 'display');
        }

        $dataSetList = model("MyData")
            ->with('getUser')
            ->where($where)
            ->order(['create_time' => 'desc'])
            ->field($field)
            ->select();
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
        $result = model("MyData")->addDataByFile($dataInfo);

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

        $result = model("MyData")->addDataByText($dataInfo);

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

        if (!in_array(2, $this->groupIds)) {
            $where = ['id' => $id, 'uid' => session('admin.id')];
        } else {
            $where = ['id' => $id];
        }

        $dataInfo = model('MyData')
            ->where($where)
            ->with('getDatas')
            ->field('uid,title,id,count')
            ->find();
        if (!$dataInfo) {
            $this->error('页面不存在');
        }
        $this->assign([
            'dataInfo' => $dataInfo,
        ]);
        return view();
    }

    public function append()
    {

        if (request()->isAjax()) {

            $id=input('post.dataId');
            if (!in_array(2, $this->groupIds)) {
                $where = ['id' => $id, 'uid' => session('admin.id')];
            } else {
                $where = ['id' => $id];
            }
            $dataInfo = model('MyData')
                ->where($where)
                ->field('uid,title,id,count')
                ->find();
            $dataInfo['dataText'] = input('post.dataText');

            $result = model("MyData")->appendDataByText($dataInfo);

            if ($result == 1) {
                $redisKey = "my_data_" . $dataInfo['id'];
                $redis = new Redis();
                $redis->del($redisKey);
                return $this->success("提交成功！", url('admin/MyData/index'));
            } else {
                return $this->error($result);
            }
        }
        $id = input('id');

        if (!in_array(2, $this->groupIds)) {
            $where = ['id' => $id, 'uid' => session('admin.id')];
        } else {
            $where = ['id' => $id];
        }
        
        $dataInfo = model('MyData')
            ->where($where)
            ->field('uid,title,id,count')
            ->find();
        if (!$dataInfo) {
            $this->error('页面不存在');
        }
        $this->assign([
            'dataInfo' => $dataInfo,
        ]);

        return view();
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
                ->where(['id' => $id])
                ->with('delDatas')
                ->field('id')
                ->find();

            $result = $dataInfo->together('delDatas')->delete();
            if ($result) {
                $this->success('删除成功', url('admin/MyData/index'));
            } else {
                $this->error('删除失败');
            }
        }
    }
}
