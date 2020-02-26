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
        $dataSetList=model("MyData")->where(['uid'=>session('admin.id')])->select();
        $this->assign([
            'dataSetList'=>$dataSetList
        ]);
        return view();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        
        return view();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    public function createByFile()
    {
        $data['dataName']=input('post.dataName');
        $data['dataFile']=input('file.dataFile');
        
        $result=model("MyData")->getDataByFile($data);

        if($result==1){
            return $this->success("提交成功！",url('admin/MyData/index'));
        }else{
            return $this->error($result);
        }
    }

    /**
     * 数据集详情
     */
    public function read()
    {
        $id=input('id');
        $dataInfo=model('MyData')->with('options')->find($id);
        $this->assign([
            'dataInfo'=>$dataInfo
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
            $id=input('post.id');
            $dataInfo=model('MyData')->with('options')->field('id')->where(['id'=>$id])->find();
            $result=$dataInfo->together('options')->delete();
            if($result){
                $this->success('删除成功',url('admin/MyData/index'));
            }else{
                $this->error('删除失败');
            }
        }
    }
}
